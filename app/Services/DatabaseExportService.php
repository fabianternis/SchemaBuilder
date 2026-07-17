<?php

namespace App\Services;

use App\Models\{SchemaDatabase as Database, SchemaTable as Table};
use Illuminate\Support\Facades\Auth;

class DatabaseExportService
{
    /**
     * Supported export targets with display labels.
     */
    public static array $targets = [
        'sql'     => ['label' => 'SQL (CREATE TABLE)',      'ext' => 'sql',  'mime' => 'text/plain'],
        'laravel' => ['label' => 'Laravel Migration',       'ext' => 'php',  'mime' => 'text/plain'],
        'json'    => ['label' => 'JSON Schema',             'ext' => 'json', 'mime' => 'application/json'],
        'csv'     => ['label' => 'CSV (column definitions)','ext' => 'csv',  'mime' => 'text/csv'],
    ];

    // -------------------------------------------------------------------------
    // Database-level helpers
    // -------------------------------------------------------------------------

    public function exportDatabase(Database $database, string $to): string
    {
        $project = $database->project;

        if (!$project || $project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $tables = $database->tables;

        // JSON wraps everything as a single structure
        if (strtolower($to) === 'json') {
            return $this->exportDatabaseJson($database, $tables);
        }

        // CSV wraps everything as one sheet with a table column
        if (strtolower($to) === 'csv') {
            return $this->exportDatabaseCsv($database, $tables);
        }

        $output_data = '';
        foreach ($tables as $table) {
            $output_data .= $this->exportTable($table, $to) . "\n\n";
        }

        return $output_data;
    }

    public function getMimeType(string $to): string
    {
        return self::$targets[strtolower($to)]['mime'] ?? 'text/plain';
    }

    public function getExtension(string $to): string
    {
        return self::$targets[strtolower($to)]['ext'] ?? 'txt';
    }

    // -------------------------------------------------------------------------
    // Table-level export (SQL / Laravel)
    // -------------------------------------------------------------------------

    public function exportTable(Table $table, string $to): string
    {
        $output_string = '';

        if ((!isset($to)) || (strtolower($to) === 'sql')) {
            $output_string = $this->exportTableSql($table);

        } elseif (strtolower($to) === 'laravel') {
            $output_string = $this->exportTableLaravel($table);

        } elseif (strtolower($to) === 'json') {
            // Per-table JSON (array of column objects)
            $output_string = json_encode($this->tableToArray($table), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } elseif (strtolower($to) === 'csv') {
            $output_string = $this->exportTableCsv($table);

        } else {
            abort(404, 'Export Target invalid / No matching export-target found');
        }

        return $output_string;
    }

    // -------------------------------------------------------------------------
    // SQL
    // -------------------------------------------------------------------------

    private function exportTableSql(Table $table): string
    {
        $output_string = "CREATE TABLE `{$table->name}` (\n";
        $columns = $table->columns()->orderBy('order_index')->get();

        $column_definitions = [];
        $foreign_keys = [];
        $primary_keys = [];

        foreach ($columns as $column) {
            $definition = "  `{$column->name}` " . strtoupper($column->type);

            if ($column->length) {
                $definition .= "({$column->length})";
            }

            if ($column->auto_increment) {
                $definition .= " AUTO_INCREMENT";
            }

            $definition .= $column->is_nullable ? " NULL" : " NOT NULL";

            if ($column->default !== null) {
                $defaultValue = is_numeric($column->default) ? $column->default : "'{$column->default}'";
                $definition .= " DEFAULT {$defaultValue}";
            }

            if ($column->is_unique) {
                $definition .= " UNIQUE";
            }

            $column_definitions[] = $definition;

            if ($column->is_primary) {
                $primary_keys[] = "`{$column->name}`";
            }

            if ($column->referenced_table_id) {
                $referencedTable = Table::find($column->referenced_table_id);
                if ($referencedTable) {
                    $fkRule = "  CONSTRAINT `fk_{$table->name}_{$column->name}` FOREIGN KEY (`{$column->name}`) REFERENCES `{$referencedTable->name}` (`id`)";
                    if ($column->on_cascade) {
                        $fkRule .= " ON DELETE " . strtoupper($column->on_cascade);
                    }
                    $foreign_keys[] = $fkRule;
                }
            }
        }

        if (!empty($primary_keys)) {
            $column_definitions[] = "  PRIMARY KEY (" . implode(', ', $primary_keys) . ")";
        }

        $output_string .= implode(",\n", array_merge($column_definitions, $foreign_keys));
        $output_string .= "\n);";

        return $output_string;
    }

    // -------------------------------------------------------------------------
    // Laravel Migration
    // -------------------------------------------------------------------------

    private function exportTableLaravel(Table $table): string
    {
        $output_string  = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n";
        $output_string .= "        Schema::create('{$table->name}', function (Blueprint \$table) {\n";

        $columns = $table->columns()->orderBy('order_index')->get();

        foreach ($columns as $column) {
            if ($column->name === 'id' && $column->is_primary && $column->auto_increment) {
                $output_string .= "            \$table->id();\n";
                continue;
            }

            $type   = strtolower($column->type);
            $method = 'string';
            $args   = "'{$column->name}'";

            switch ($type) {
                case 'int':
                case 'integer':
                    $method = 'integer'; break;
                case 'bigint':
                    $method = 'bigInteger'; break;
                case 'text':
                case 'longtext':
                    $method = 'text'; break;
                case 'boolean':
                case 'tinyint':
                    $method = 'boolean'; break;
                case 'date':
                    $method = 'date'; break;
                case 'datetime':
                case 'timestamp':
                    $method = 'timestamp'; break;
                case 'decimal':
                case 'float':
                case 'double':
                    $method = $type; break;
                case 'varchar':
                default:
                    $method = 'string';
                    if ($column->length) {
                        $args .= ", {$column->length}";
                    }
                    break;
            }

            $def = "            \$table->{$method}({$args})";

            if ($column->auto_increment)  { $def .= "->autoIncrement()"; }
            if ($column->is_nullable)     { $def .= "->nullable()"; }

            if ($column->default !== null) {
                $defaultValue = is_numeric($column->default) ? $column->default : "'{$column->default}'";
                $def .= "->default({$defaultValue})";
            }

            if ($column->is_unique)  { $def .= "->unique()"; }
            if ($column->is_primary) { $def .= "->primary()"; }

            $output_string .= $def . ";\n?>";
        }

        foreach ($columns as $column) {
            if ($column->referenced_table_id) {
                $referencedTable = Table::find($column->referenced_table_id);
                if ($referencedTable) {
                    $fkDef = "            \$table->foreign('{$column->name}')->references('id')->on('{$referencedTable->name}')";
                    if ($column->on_cascade) {
                        $fkDef .= "->onDelete('" . strtolower($column->on_cascade) . "')";
                    }
                    $output_string .= $fkDef . ";\n";
                }
            }
        }

        $output_string .= "        });\n    }\n\n";
        $output_string .= "    public function down(): void\n    {\n";
        $output_string .= "        Schema::dropIfExists('{$table->name}');\n    }\n};\n";

        return $output_string;
    }

    // -------------------------------------------------------------------------
    // JSON
    // -------------------------------------------------------------------------

    private function tableToArray(Table $table): array
    {
        $columns = $table->columns()->orderBy('order_index')->get();

        return [
            'table'   => $table->name,
            'columns' => $columns->map(fn ($c) => [
                'name'                => $c->name,
                'type'                => $c->type,
                'length'              => $c->length,
                'is_nullable'         => $c->is_nullable,
                'is_primary'          => $c->is_primary,
                'is_unique'           => $c->is_unique,
                'auto_increment'      => $c->auto_increment,
                'default'             => $c->default,
                'on_cascade'          => $c->on_cascade,
                'referenced_table_id' => $c->referenced_table_id,
            ])->toArray(),
        ];
    }

    private function exportDatabaseJson(Database $database, $tables): string
    {
        $schema = [
            'database' => $database->name,
            'tables'   => $tables->map(fn ($t) => $this->tableToArray($t))->values()->toArray(),
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // -------------------------------------------------------------------------
    // CSV
    // -------------------------------------------------------------------------

    private function exportTableCsv(Table $table): string
    {
        $rows    = [];
        $rows[]  = ['table', 'column', 'type', 'length', 'nullable', 'primary', 'unique', 'auto_increment', 'default', 'on_cascade', 'references_table'];

        $columns = $table->columns()->orderBy('order_index')->get();

        foreach ($columns as $column) {
            $referencedTable = $column->referenced_table_id ? Table::find($column->referenced_table_id)?->name : '';
            $rows[] = [
                $table->name,
                $column->name,
                $column->type,
                $column->length ?? '',
                $column->is_nullable ? 'YES' : 'NO',
                $column->is_primary ? 'YES' : 'NO',
                $column->is_unique  ? 'YES' : 'NO',
                $column->auto_increment ? 'YES' : 'NO',
                $column->default ?? '',
                $column->on_cascade ?? '',
                $referencedTable ?? '',
            ];
        }

        return $this->arrayToCsvString($rows);
    }

    private function exportDatabaseCsv(Database $database, $tables): string
    {
        $rows   = [];
        $rows[] = ['table', 'column', 'type', 'length', 'nullable', 'primary', 'unique', 'auto_increment', 'default', 'on_cascade', 'references_table'];

        foreach ($tables as $table) {
            $columns = $table->columns()->orderBy('order_index')->get();
            foreach ($columns as $column) {
                $referencedTable = $column->referenced_table_id ? Table::find($column->referenced_table_id)?->name : '';
                $rows[] = [
                    $table->name,
                    $column->name,
                    $column->type,
                    $column->length ?? '',
                    $column->is_nullable ? 'YES' : 'NO',
                    $column->is_primary ? 'YES' : 'NO',
                    $column->is_unique  ? 'YES' : 'NO',
                    $column->auto_increment ? 'YES' : 'NO',
                    $column->default ?? '',
                    $column->on_cascade ?? '',
                    $referencedTable ?? '',
                ];
            }
        }

        return $this->arrayToCsvString($rows);
    }

    private function arrayToCsvString(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }
}