<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->words(2, true);
        return [
            'owner_id'       => User::factory(),
            'owner_type'     => 'App\\Models\\User',
            'name'           => $name,
            'slug'           => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'description'    => fake()->optional()->sentence(),
            'production_url' => fake()->optional()->url(),
            'repo_url'       => fake()->optional()->url(),
            'preferences'    => null,
        ];
    }
}
