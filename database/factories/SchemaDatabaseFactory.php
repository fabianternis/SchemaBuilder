<?php

namespace Database\Factories;

use App\Models\{SchemaDatabase, Project};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchemaDatabase>
 */
class SchemaDatabaseFactory extends Factory
{
    protected $model = SchemaDatabase::class;

    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'name'        => fake()->unique()->slug(2),
            'displayname' => fake()->optional()->words(2, true),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
