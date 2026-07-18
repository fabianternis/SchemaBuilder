<?php

namespace App\Models;

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

    public function table(): BelongsTo
    {
        return $this->belongsTo(SchemaTable::class, 'table_id');
    }

    public function referencedTable(): BelongsTo
    {
        return $this->belongsTo(SchemaTable::class, 'referenced_table_id');
    }
}
