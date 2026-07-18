<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Concerns\HasUlids, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class SchemaTable extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'database_id',
        'name',
    ];

    public function database(): BelongsTo
    {
        return $this->belongsTo(SchemaDatabase::class, 'database_id');
    }

    public function columns(): HasMany
    {
        return $this->hasMany(SchemaColumn::class, 'table_id');
    }
}
