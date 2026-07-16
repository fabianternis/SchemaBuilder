<?php

use App\Models\{User, Project, SchemaDatabase, SchemaTable, SchemaColumn};

// ---------------------------------------------------------------------------
// Schema — Quick Create / Store
// ---------------------------------------------------------------------------

it('shows the quick-create form', function () {
    loginUser();
    $response = $this->get(route('new'));
    $response->assertStatus(200);
});

it('quick-stores a database under a new project', function () {
    $user = loginUser();

    $response = $this->post(route('schema.storeNew'), [
        'project'      => 'create_new',
        'project_name' => 'New Project',
        'name'         => 'main_db',
        'displayname'  => null,
        'description'  => null,
    ]);

    $project = Project::where('name', 'New Project')->first();
    expect($project)->not->toBeNull();
    expect($project->owner_id)->toBe($user->id);

    $db = SchemaDatabase::where('name', 'main_db')->first();
    expect($db)->not->toBeNull();
    expect($db->project_id)->toBe($project->id);

    $response->assertRedirect(route('schema.database', [
        'project'  => $project->slug,
        'database' => 'main_db',
    ]));
});

it('quick-stores a database under an existing project', function () {
    $user    = loginUser();
    $project = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

    $response = $this->post(route('schema.storeNew'), [
        'project'     => $project->id,
        'name'        => 'secondary_db',
        'displayname' => null,
        'description' => null,
    ]);

    $this->assertDatabaseHas('schema_databases', [
        'project_id' => $project->id,
        'name'       => 'secondary_db',
    ]);
    $response->assertRedirect();
});

// ---------------------------------------------------------------------------
// Schema — Show Project
// ---------------------------------------------------------------------------

it('shows the project schema page', function () {
    $user    = loginUser();
    $project = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

    $response = $this->get(route('schema.project', $project));
    $response->assertStatus(200);
});

it('denies access to another user\'s project schema', function () {
    $user    = loginUser();
    $other   = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $other->id, 'owner_type' => User::class]);

    $response = $this->get(route('schema.project', $project));
    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// Schema — Show Database
// ---------------------------------------------------------------------------

it('shows the database schema page', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);

    $response = $this->get(route('schema.database', [
        'project'  => $project->slug,
        'database' => $database->name,
    ]));
    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Schema — Store Table
// ---------------------------------------------------------------------------

it('stores a new table in a database', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);

    $response = $this->post(route('schema.table.store', [
        'project'  => $project->slug,
        'database' => $database->name,
    ]), ['name' => 'orders']);

    $this->assertDatabaseHas('schema_tables', [
        'database_id' => $database->id,
        'name'        => 'orders',
    ]);
    $response->assertRedirect();
});

it('deduplicates table names within the same database', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);

    // Create the first table
    SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'products']);

    // Attempt to create another with the same name
    $this->post(route('schema.table.store', [
        'project'  => $project->slug,
        'database' => $database->name,
    ]), ['name' => 'products']);

    $tables = SchemaTable::where('database_id', $database->id)->get();
    expect($tables->pluck('name')->unique()->count())->toBe(2);
    expect($tables->last()->name)->toBe('products_1');
});

// ---------------------------------------------------------------------------
// Schema — Show Table
// ---------------------------------------------------------------------------

it('shows the table editor page', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id]);

    $response = $this->get(route('schema.table', [
        'project'  => $project->slug,
        'database' => $database->name,
        'table'    => $table->name,
    ]));
    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Schema — Update Table (JSON API)
// ---------------------------------------------------------------------------

it('saves table name and columns via JSON API', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id]);

    $response = $this->putJson(route('schema.table.update', [
        'project'  => $project->slug,
        'database' => $database->name,
        'table'    => $table->name,
    ]), [
        'name'    => 'renamed_table',
        'columns' => [
            [
                'id'           => null,
                'name'         => 'id',
                'type'         => 'bigint',
                'is_nullable'  => false,
                'is_primary'   => true,
                'is_unique'    => true,
                'auto_increment' => true,
                'default'      => null,
                'length'       => null,
                'on_cascade'   => null,
                'referenced_table_id' => null,
            ],
            [
                'id'           => null,
                'name'         => 'email',
                'type'         => 'string',
                'is_nullable'  => false,
                'is_primary'   => false,
                'is_unique'    => true,
                'auto_increment' => false,
                'default'      => null,
                'length'       => 255,
                'on_cascade'   => null,
                'referenced_table_id' => null,
            ],
        ],
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    // Table was renamed
    $this->assertDatabaseHas('schema_tables', ['id' => $table->id, 'name' => 'renamed_table']);

    // Columns were created
    $this->assertDatabaseHas('schema_columns', ['name' => 'id', 'type' => 'bigint']);
    $this->assertDatabaseHas('schema_columns', ['name' => 'email', 'type' => 'string', 'length' => 255]);
});

it('returns 403 on table update for another user\'s project', function () {
    loginUser();
    $other    = User::factory()->create();
    $project  = Project::factory()->create(['owner_id' => $other->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id]);

    $response = $this->putJson(route('schema.table.update', [
        'project'  => $project->slug,
        'database' => $database->name,
        'table'    => $table->name,
    ]), ['name' => $table->name, 'columns' => []]);

    $response->assertStatus(403);
});

// ---------------------------------------------------------------------------
// Schema — Show Column
// ---------------------------------------------------------------------------

it('shows the column editor page', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id]);
    $column   = SchemaColumn::factory()->create(['table_id' => $table->id]);

    $response = $this->get(route('schema.column', [
        'project'  => $project->slug,
        'database' => $database->name,
        'table'    => $table->name,
        'column'   => $column->name,
    ]));
    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Schema — Update Column (JSON API)
// ---------------------------------------------------------------------------

it('saves a single column via JSON API', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id]);
    $column   = SchemaColumn::factory()->create(['table_id' => $table->id, 'name' => 'email', 'type' => 'string']);

    $response = $this->putJson(route('schema.column.update', [
        'project'  => $project->slug,
        'database' => $database->name,
        'table'    => $table->name,
        'column'   => $column->name,
    ]), [
        'name'          => 'email_address',
        'type'          => 'string',
        'is_nullable'   => true,
        'is_primary'    => false,
        'is_unique'     => false,
        'auto_increment' => false,
        'default'       => null,
        'length'        => 191,
        'on_cascade'    => null,
        'referenced_table_id' => null,
    ]);

    $response->assertOk()->assertJson(['success' => true]);
    $this->assertDatabaseHas('schema_columns', [
        'id'     => $column->id,
        'name'   => 'email_address',
        'length' => 191,
    ]);
});

// ---------------------------------------------------------------------------
// Schema — Tables List (FK selector API)
// ---------------------------------------------------------------------------

it('returns table list JSON for FK selectors', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'users']);

    $response = $this->getJson(route('schema.tables.list', [
        'project'  => $project->slug,
        'database' => $database->name,
    ]));

    $response->assertOk()
        ->assertJsonStructure(['tables' => [['id', 'name']]])
        ->assertJsonFragment(['name' => 'users']);
});

// ---------------------------------------------------------------------------
// Schema — Export
// ---------------------------------------------------------------------------

it('exports a database schema as SQL', function () {
    $user     = loginUser();
    $project  = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $database = SchemaDatabase::factory()->create(['project_id' => $project->id]);
    $table    = SchemaTable::factory()->create(['database_id' => $database->id, 'name' => 'items']);
    SchemaColumn::factory()->create([
        'table_id'    => $table->id,
        'name'        => 'id',
        'type'        => 'bigint',
        'is_primary'  => true,
        'is_nullable' => false,
        'order_index' => 0,
    ]);

    $response = $this->get(route('schema.export', [
        'project'  => $project->slug,
        'database' => $database->name,
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    expect($response->getContent())->toContain('CREATE TABLE `items`');
});
