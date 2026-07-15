<?php

namespace App\Services;

use App\Models\SchemaDatabase as Database;
use App\Models\SchemaTable as Table;
use Illuminate\Support\Facades\Auth;

class DatabaseExportService
{
    public function exportDatabase(Database $database, string $to): string
    {
        $project = $database->project;

        if (!$project || $project->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $tables = $database->tables;
        $output_data = '';

        foreach ($tables as $table) {
            $output_data .= $this->exportTable($table, $to) . "\n\n";
        }

        return $output_data;
    }

    public function exportTable(Table $table, string $to): string
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
}