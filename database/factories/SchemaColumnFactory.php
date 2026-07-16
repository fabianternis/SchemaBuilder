<?php

namespace Database\Factories;

use App\Models\SchemaColumn;
use App\Models\SchemaTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchemaColumn>
 */
class SchemaColumnFactory extends Factory
{
    protected $model = SchemaColumn::class;

    private static array $columnTypes = [
        'bigint', 'boolean', 'char', 'date', 'dateTime', 'decimal',
        'double', 'float', 'integer', 'json', 'longText', 'string',
        'text', 'timestamp', 'tinyInteger', 'uuid',
    ];

    public function definition(): array
    {
        return [
            'table_id'            => SchemaTable::factory(),
            'name'                => fake()->unique()->word(),
            'type'                => fake()->randomElement(self::$columnTypes),
            'is_nullable'         => fake()->boolean(),
            'is_primary'          => false,
            'is_unique'           => fake()->boolean(20),
            'auto_increment'      => false,
            'default'             => null,
            'length'              => null,
            'on_cascade'          => null,
            'referenced_table_id' => null,
            'order_index'         => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * Mark column as primary key.
     */
    public function primaryKey(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary'    => true,
            'auto_increment' => true,
            'is_nullable'   => false,
            'name'          => 'id',
            'type'          => 'bigint',
        ]);
    }
}
