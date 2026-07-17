<?php

namespace App\Services;

use App\Models\{SchemaDatabase as Database, SchemaTable as Table};
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
        $output_string = '';
        if((!isset($to)) || (strtolower($to) == 'sql')) {
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
    
        } elseif(strtolower($to) == 'laravel') {
            $output_string .= "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n";
            $output_string .= "        Schema::create('{$table->name}', function (Blueprint \$table) {\n";
            
            $columns = $table->columns()->orderBy('order_index')->get();

            foreach ($columns as $column) {
                if ($column->name === 'id' && $column->is_primary && $column->auto_increment) {
                    $output_string .= "            \$table->id();\n";
                    continue;
                }

                $type = strtolower($column->type);
                $method = 'string';
                $args = "'{$column->name}'";

                switch($type) {
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

                if ($column->auto_increment) {
                    $def .= "->autoIncrement()";
                }
    
                if ($column->is_nullable) {
                    $def .= "->nullable()";
                }
    
                if ($column->default !== null) {
                    $defaultValue = is_numeric($column->default) ? $column->default : "'{$column->default}'";
                    $def .= "->default({$defaultValue})";
                }
    
                if ($column->is_unique) {
                    $def .= "->unique()";
                }
    
                if ($column->is_primary) {
                    $def .= "->primary()";
                }

                $output_string .= $def . ";\n";
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
            
        } else {
            abort(404, 'Export Target invalid / No matching export-target found');
        }

        return $output_string;
    }
}