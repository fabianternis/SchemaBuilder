<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'owner_type',
        'name',
        'slug',
        'description',
        'production_url',
        'repo_url',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function databases(): HasMany
    {
        return $this->hasMany(SchemaDatabase::class, 'project_id');
    }
}
