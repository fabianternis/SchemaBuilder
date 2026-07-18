<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\{HasUlids, HasFactory, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class SchemaDatabase extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'name',
        'displayname',
        'description',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(SchemaTable::class, 'database_id');
    }
}
