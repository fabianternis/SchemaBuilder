<?php

use App\Models\{User, Project, SchemaDatabase, SchemaTable, SchemaColumn};
use App\Services\DatabaseExportService;
use Illuminate\Support\Facades\Auth;

// ---------------------------------------------------------------------------
// DatabaseExportService — exportTable
// ---------------------------------------------------------------------------

it('exports a table with basic columns', function () {
    $user     = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'products']);

    SchemaColumn::factory()->create([
        'table_id'    => $table->id,
        'name'        => 'id',
        'type'        => 'bigint',
        'is_nullable' => false,
        'is_primary'  => true,
        'auto_increment' => true,
        'order_index' => 0,
    ]);

    SchemaColumn::factory()->create([
        'table_id'    => $table->id,
        'name'        => 'name',
        'type'        => 'string',
        'is_nullable' => false,
        'length'      => 255,
        'order_index' => 1,
    ]);

    $service = new DatabaseExportService();
    $sql = $service->exportTable($table, 'sql');

    expect($sql)->toContain('CREATE TABLE `products`');
    expect($sql)->toContain('`id` BIGINT AUTO_INCREMENT NOT NULL');
    expect($sql)->toContain('`name` STRING(255) NOT NULL');
    expect($sql)->toContain('PRIMARY KEY (`id`)');
});

it('includes UNIQUE modifier in exported SQL', function () {
    $user     = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'users']);

    SchemaColumn::factory()->create([
        'table_id'    => $table->id,
        'name'        => 'email',
        'type'        => 'string',
        'is_unique'   => true,
        'is_nullable' => false,
        'order_index' => 0,
    ]);

    $service = new DatabaseExportService();
    $sql = $service->exportTable($table, 'sql');

    expect($sql)->toContain('UNIQUE');
});

it('includes DEFAULT value in exported SQL', function () {
    $user     = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'settings']);

    SchemaColumn::factory()->create([
        'table_id'    => $table->id,
        'name'        => 'active',
        'type'        => 'boolean',
        'is_nullable' => false,
        'default'     => '1',
        'order_index' => 0,
    ]);

    $service = new DatabaseExportService();
    $sql = $service->exportTable($table, 'sql');

    expect($sql)->toContain('DEFAULT 1');
});

it('includes NULL constraint for nullable column', function () {
    $user     = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id]);

    SchemaColumn::factory()->create([
        'table_id'    => $table->id,
        'name'        => 'bio',
        'type'        => 'text',
        'is_nullable' => true,
        'order_index' => 0,
    ]);

    $service = new DatabaseExportService();
    $sql = $service->exportTable($table, 'sql');

    expect($sql)->toContain('NULL')
        ->and($sql)->not->toContain('NOT NULL');
});

it('includes FOREIGN KEY constraint in exported SQL', function () {
    $user     = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);

    $usersTable = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'users']);
    $ordersTable = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'orders']);

    SchemaColumn::factory()->create([
        'table_id'            => $ordersTable->id,
        'name'                => 'user_id',
        'type'                => 'bigint',
        'is_nullable'         => false,
        'order_index'         => 0,
        'referenced_table_id' => $usersTable->id,
        'on_cascade'          => 'CASCADE',
    ]);

    $service = new DatabaseExportService();
    $sql = $service->exportTable($ordersTable, 'sql');

    expect($sql)->toContain('FOREIGN KEY (`user_id`)');
    expect($sql)->toContain('REFERENCES `users`');
    expect($sql)->toContain('ON DELETE CASCADE');
});

// ---------------------------------------------------------------------------
// DatabaseExportService — exportDatabase (auth guard)
// ---------------------------------------------------------------------------

it('aborts with 403 when exporting another user\'s database', function () {
    $owner    = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $owner->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);

    $other = User::factory()->create();
    Auth::login($other);

    $service = new DatabaseExportService();
    $service->exportDatabase($database, 'sql');
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('exports all tables in a database', function () {
    $user     = User::factory()->create();
    Auth::login($user);

    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);

    $table1 = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'alpha']);
    $table2 = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'beta']);

    SchemaColumn::factory()->create(['table_id' => $table1->id, 'name' => 'id', 'type' => 'bigint', 'order_index' => 0]);
    SchemaColumn::factory()->create(['table_id' => $table2->id, 'name' => 'id', 'type' => 'bigint', 'order_index' => 0]);

    $service = new DatabaseExportService();
    $sql = $service->exportDatabase($database, 'sql');

    expect($sql)->toContain('CREATE TABLE `alpha`')
        ->and($sql)->toContain('CREATE TABLE `beta`');
});
