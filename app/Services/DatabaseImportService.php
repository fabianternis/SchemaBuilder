<?php

namespace App\Services;

use App\Models\{SchemaDatabase as Database, SchemaTable as Table, SchemaColumn as Column};
use Illuminate\Support\Facades\Auth;

class DatabaseImportService
{
    /**
     * Supported import formats with display labels.
     */
    public static array $sources = [
        'sql'  => ['label' => 'SQL (CREATE TABLE statements)', 'accept' => '.sql,.txt'],
        'json' => ['label' => 'JSON Schema (SchemaBuilder export)', 'accept' => '.json'],
        'csv'  => ['label' => 'CSV (column definitions)', 'accept' => '.csv'],
    ];

    // -------------------------------------------------------------------------
    // Entry-point: detect format and dispatch
    // -------------------------------------------------------------------------

    /**
     * Import a schema from raw content into $database.
     *
     * Returns an array like:
     *   ['tables_created' => int, 'columns_created' => int, 'warnings' => string[]]
     */
    public function import(Database $database, string $from, string $content): array
    {
        $project = $database->project;
        if (!$project || $project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return match (strtolower($from)) {
            'sql'   => $this->importSql($database, $content),
            'json'  => $this->importJson($database, $content),
            'csv'   => $this->importCsv($database, $content),
            default => abort(422, "Unknown import format: {$from}"),
        };
    }

    // -------------------------------------------------------------------------
    // SQL import
    // -------------------------------------------------------------------------

    private function importSql(Database $database, string $sql): array
    {
        $stats    = ['tables_created' => 0, 'columns_created' => 0, 'warnings' => []];

        // Extract all CREATE TABLE blocks (handles multi-line, backtick-quoted names)
        preg_match_all(
            '/CREATE\s+TABLE\s+`?(\w+)`?\s*\((.*?)\)\s*;/is',
            $sql,
            $matches,
            PREG_SET_ORDER
        );

        if (empty($matches)) {
            $stats['warnings'][] = 'No valid CREATE TABLE statements found in the uploaded file.';
            return $stats;
        }

        foreach ($matches as $match) {
            $tableName = $match[1];
            $body      = $match[2];

            $table = $this->upsertTable($database, $tableName);
            $stats['tables_created']++;

            $columnIndex = 0;
            $lines = preg_split('/,\s*\n/s', $body);

            foreach ($lines as $line) {
                $line = trim($line);

                // Skip PRIMARY KEY / CONSTRAINT / KEY lines as standalone definitions
                if (preg_match('/^(PRIMARY\s+KEY|KEY|UNIQUE\s+KEY|CONSTRAINT|INDEX)/i', $line)) {
                    // Parse PRIMARY KEY to flag the column
                    if (preg_match('/^PRIMARY\s+KEY\s*\(`?(\w+)`?\)/i', $line, $pkMatch)) {
                        Column::where('table_id', $table->id)
                            ->where('name', $pkMatch[1])
                            ->update(['is_primary' => true]);
                    }
                    continue;
                }

                // Column definition: `name` TYPE(...) modifiers
                if (!preg_match('/^`?(\w+)`?\s+(\w+)/i', $line, $colMatch)) {
                    continue;
                }

                $colName = $colMatch[1];
                $colType = strtolower($colMatch[2]);
                $rest    = substr($line, strlen($colMatch[0]));

                // Length
                $length = null;
                if (preg_match('/^\s*\((\d+)\)/i', $rest, $lenMatch)) {
                    $length = (int) $lenMatch[1];
                    $rest   = substr($rest, strlen($lenMatch[0]));
                }

                $attrs = [
                    'table_id'       => $table->id,
                    'name'           => $colName,
                    'type'           => $colType,
                    'length'         => $length,
                    'is_nullable'    => stripos($rest, 'NOT NULL') === false,
                    'is_primary'     => stripos($rest, 'PRIMARY KEY') !== false,
                    'is_unique'      => stripos($rest, 'UNIQUE') !== false,
                    'auto_increment' => stripos($rest, 'AUTO_INCREMENT') !== false,
                    'default'        => null,
                    'on_cascade'     => null,
                    'order_index'    => $columnIndex++,
                ];

                // DEFAULT value
                if (preg_match('/DEFAULT\s+\'([^\']*)\'/i', $rest, $defMatch)) {
                    $attrs['default'] = $defMatch[1];
                } elseif (preg_match('/DEFAULT\s+(\S+)/i', $rest, $defMatch)) {
                    $attrs['default'] = $defMatch[1];
                }

                $existingCol = Column::where('table_id', $table->id)->where('name', $colName)->first();
                if ($existingCol) {
                    $existingCol->update($attrs);
                } else {
                    Column::create($attrs);
                    $stats['columns_created']++;
                }
            }
        }

        return $stats;
    }

    // -------------------------------------------------------------------------
    // JSON import
    // -------------------------------------------------------------------------

    private function importJson(Database $database, string $content): array
    {
        $stats = ['tables_created' => 0, 'columns_created' => 0, 'warnings' => []];

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            abort(422, 'Invalid JSON: ' . json_last_error_msg());
        }

        // Support both single-table array and full-database structure
        $tables = [];
        if (isset($data['tables'])) {
            // Full-database export: { "database": "...", "tables": [...] }
            $tables = $data['tables'];
        } elseif (isset($data['table'])) {
            // Single-table export: { "table": "...", "columns": [...] }
            $tables = [$data];
        } elseif (is_array($data) && isset($data[0]['table'])) {
            // Array of table objects
            $tables = $data;
        } else {
            $stats['warnings'][] = 'Unrecognized JSON structure. Expected a SchemaBuilder JSON export.';
            return $stats;
        }

        foreach ($tables as $tableData) {
            $tableName = $tableData['table'] ?? null;
            if (!$tableName) {
                $stats['warnings'][] = 'Skipped a table entry with no "table" key.';
                continue;
            }

            $table = $this->upsertTable($database, $tableName);
            $stats['tables_created']++;

            $columnIndex = 0;
            foreach ($tableData['columns'] ?? [] as $colData) {
                $colName = $colData['name'] ?? null;
                if (!$colName) continue;

                $attrs = [
                    'table_id'            => $table->id,
                    'name'                => $colName,
                    'type'                => $colData['type']            ?? 'varchar',
                    'length'              => $colData['length']           ?? null,
                    'is_nullable'         => $colData['is_nullable']      ?? false,
                    'is_primary'          => $colData['is_primary']       ?? false,
                    'is_unique'           => $colData['is_unique']        ?? false,
                    'auto_increment'      => $colData['auto_increment']   ?? false,
                    'default'             => $colData['default']          ?? null,
                    'on_cascade'          => $colData['on_cascade']       ?? null,
                    'referenced_table_id' => null, // resolved below
                    'order_index'         => $columnIndex++,
                ];

                // Attempt to resolve referenced_table_id by name within this DB
                if (!empty($colData['referenced_table_id'])) {
                    // It might be a raw ID (same install) or a table name (cross-install)
                    $refTable = Table::find($colData['referenced_table_id'])
                        ?? Table::where('database_id', $database->id)
                               ->where('name', $colData['referenced_table_id'])
                               ->first();
                    $attrs['referenced_table_id'] = $refTable?->id;
                }

                $existingCol = Column::where('table_id', $table->id)->where('name', $colName)->first();
                if ($existingCol) {
                    $existingCol->update($attrs);
                } else {
                    Column::create($attrs);
                    $stats['columns_created']++;
                }
            }
        }

        return $stats;
    }

    // -------------------------------------------------------------------------
    // CSV import
    // -------------------------------------------------------------------------

    private function importCsv(Database $database, string $content): array
    {
        $stats = ['tables_created' => 0, 'columns_created' => 0, 'warnings' => []];

        $rows = [];
        $lines = explode("\n", str_replace("\r\n", "\n", trim($content)));
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $rows[] = str_getcsv($line);
        }

        if (empty($rows)) {
            $stats['warnings'][] = 'CSV file is empty.';
            return $stats;
        }

        // First row is header
        $header = array_map('strtolower', array_map('trim', $rows[0]));
        $expected = ['table', 'column', 'type'];
        foreach ($expected as $col) {
            if (!in_array($col, $header, true)) {
                abort(422, "CSV is missing required header column: \"{$col}\". Expected headers: table, column, type, length, nullable, primary, unique, auto_increment, default, on_cascade, references_table");
            }
        }

        $idx = array_flip($header);

        $tableCache   = [];
        $columnCounts = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (count($row) < count($expected)) continue;

            $pad = fn(string $key) => trim($row[$idx[$key] ?? -1] ?? '');

            $tableName = $pad('table');
            $colName   = $pad('column');
            $colType   = $pad('type') ?: 'varchar';

            if (!$tableName || !$colName) continue;

            // Upsert table (cache per name)
            if (!isset($tableCache[$tableName])) {
                $tableCache[$tableName]   = $this->upsertTable($database, $tableName);
                $columnCounts[$tableName] = Column::where('table_id', $tableCache[$tableName]->id)->count();
                $stats['tables_created']++;
            }

            $table = $tableCache[$tableName];

            $yesno = fn(string $key) => strtolower($pad($key)) === 'yes';

            $attrs = [
                'table_id'       => $table->id,
                'name'           => $colName,
                'type'           => $colType,
                'length'         => ($v = $pad('length')) !== '' ? (int) $v : null,
                'is_nullable'    => $yesno('nullable'),
                'is_primary'     => $yesno('primary'),
                'is_unique'      => $yesno('unique'),
                'auto_increment' => $yesno('auto_increment'),
                'default'        => ($v = $pad('default')) !== '' ? $v : null,
                'on_cascade'     => ($v = $pad('on_cascade')) !== '' ? $v : null,
                'order_index'    => $columnCounts[$tableName]++,
            ];

            // Resolve reference by table name
            if ($refName = $pad('references_table')) {
                $refTable = Table::where('database_id', $database->id)->where('name', $refName)->first();
                $attrs['referenced_table_id'] = $refTable?->id;
            }

            $existingCol = Column::where('table_id', $table->id)->where('name', $colName)->first();
            if ($existingCol) {
                $existingCol->update($attrs);
            } else {
                Column::create($attrs);
                $stats['columns_created']++;
            }
        }

        return $stats;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Find or create a table by name within a database.
     * If a table with the exact name already exists, it is reused (columns will be merged).
     */
    private function upsertTable(Database $database, string $name): Table
    {
        return Table::firstOrCreate(
            ['database_id' => $database->id, 'name' => $name],
            ['database_id' => $database->id, 'name' => $name]
        );
    }
}
