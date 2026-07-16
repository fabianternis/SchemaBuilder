<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
