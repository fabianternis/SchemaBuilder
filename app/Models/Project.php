<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes, Factories\HasFactory, Concerns\HasUlids};
use Illuminate\Database\Eloquent\Relations\{HasMany, MorphTo};
// use Illuminate\Database\Eloquent\{Model, SoftDeletes, Factories\HasFactory, Concerns\HasUlids, Relations\{HasMany, MorphTo}};

class Project extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    // protected $table = 'projects';

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

public $schema = [
        'name' => 'projects',
        'base_route' => 'projects',
        'columns' => [
            [
                'name' => 'name',
                'type' => 'text',
                'required' => true,
                'on_index' => true,
                // 'placeholder' => 'Proje'
            ],
            [
                'name' => 'slug',
                'type' => 'text',
                'required' => false,
                'on_index' => false,
            ],
            [
                'name' => 'description',
                'type' => 'textarea',
                'required' => false,
                'on_index' => true,
            ],
            [
                'name' => 'production_url',
                'type' => 'url',
                'required' => false,
                'on_index' => false,
            ],
            [
                'name' => 'repo_url',
                'type' => 'url',
                'required' => false,
                'on_index' => false,
            ],
        ],
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
