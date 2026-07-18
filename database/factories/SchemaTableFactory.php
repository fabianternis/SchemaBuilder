<?php

namespace Database\Factories;

use App\Models\{SchemaTable, SchemaDatabase};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchemaTable>
 */
class SchemaTableFactory extends Factory
{
    protected $model = SchemaTable::class;

    public function definition(): array
    {
        return [
            'database_id' => SchemaDatabase::factory(),
            'name'        => fake()->unique()->word() . '_' . fake()->numberBetween(1, 999),
        ];
    }
}
