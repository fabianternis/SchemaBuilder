<?php

namespace App\Models;

use App\Support\ColumnTypeRules;
use Illuminate\Database\Eloquent\{Concerns\HasUlids, Factories\HasFactory, Model, Relations\BelongsTo, SoftDeletes};

class SchemaColumn extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'table_id',
        'name',
        'type',
        'is_nullable',
        'is_primary',
        'default',
        'is_unique',
        'on_cascade',
        'length',
        'auto_increment',
        'referenced_table_id',
        'order_index',
    ];

    protected $casts = [
        'is_nullable' => 'boolean',
        'is_primary' => 'boolean',
        'is_unique' => 'boolean',
        'auto_increment' => 'boolean',
        'length' => 'integer',
        'order_index' => 'integer',
    ];

    public const VALID_TYPES = [
        'bigint',
        'bigIncrements',
        'binary',
        'boolean',
        'char',
        'date',
        'dateTime',
        'decimal',
        'double',
        'enum',
        'float',
        'foreignId',
        'id',
        'integer',
        'json',
        'jsonb',
        'longText',
        'mediumInteger',
        'mediumText',
        'nullableTimestamps',
        'smallInteger',
        'softDeletes',
        'string',
        'text',
        'time',
        'timestamp',
        'timestamps',
        'tinyInteger',
        'tinyText',
        'unsignedBigInteger',
        'unsignedInteger',
        'uuid',
        'ulid',
        'year',
    ];

    public static array $allowedOptions = [
        'length' => [
            'varchar', 'string', 'char', 'decimal', 'float', 'double',
            // NOT: text, longText, mediumText, tinyText, boolean, enum, json, …
        ],
        'auto_increment' => [
            'integer', 'int', 'bigint', 'bigIncrements',
            'unsignedBigInteger', 'unsignedInteger',
            'smallInteger', 'mediumInteger', 'tinyInteger',
        ],
        'is_primary'  => null,
        'is_unique'   => null,
        'is_nullable' => null,
        'default'     => null,
        'referenced_table_id' => [
            'foreignId', 'unsignedBigInteger', 'unsignedInteger',
            'integer', 'bigint', 'uuid', 'ulid',
        ],
    ];

    // -------------------------------------------------------------------------
    // Model hooks: silently sanitise invalid states on every save
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (SchemaColumn $col) {
            $sanitised = ColumnTypeRules::sanitise($col->getAttributes());
            foreach ($sanitised as $key => $value) {
                $col->setAttribute($key, $value);
            }
        });
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(SchemaTable::class, 'table_id');
    }

    public function referencedTable(): BelongsTo
    {
        return $this->belongsTo(SchemaTable::class, 'referenced_table_id');
    }
}
