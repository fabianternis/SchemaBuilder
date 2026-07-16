<?php

use App\Models\{User, Project};
use Illuminate\Support\Str;

// ---------------------------------------------------------------------------
// Projects — Index
// ---------------------------------------------------------------------------

it('shows only authenticated user\'s projects on index', function () {
    $user  = loginUser();
    $other = User::factory()->create();

    $myProject    = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
    $theirProject = Project::factory()->create(['owner_id' => $other->id, 'owner_type' => User::class]);

    $response = $this->get(route('projects.index'));

    $response->assertStatus(200);
    $response->assertSee($myProject->name);
    $response->assertDontSee($theirProject->name);
});

it('redirects guests from project index', function () {
    $response = $this->get(route('projects.index'));
    // Auth middleware redirects to the named route 'login' (which is /auth in this app)
    $response->assertRedirect();
});

// ---------------------------------------------------------------------------
// Projects — Create
// ---------------------------------------------------------------------------

it('shows the create project form', function () {
    loginUser();
    $response = $this->get(route('projects.create'));
    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Projects — Store
// ---------------------------------------------------------------------------

it('stores a new project', function () {
    $user = loginUser();

    $response = $this->post(route('projects.store'), [
        'name'        => 'My Test Project',
        'slug'        => '',
        'description' => 'A cool project.',
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', [
        'name'       => 'My Test Project',
        'owner_id'   => $user->id,
    ]);
});

it('auto-generates a slug on store when not provided', function () {
    $user = loginUser();

    $this->post(route('projects.store'), [
        'name'        => 'Auto Slug Test',
        'slug'        => '',
        'description' => null,
    ]);

    $this->assertDatabaseHas('projects', [
        'name'     => 'Auto Slug Test',
        'owner_id' => $user->id,
    ]);

    $project = Project::where('name', 'Auto Slug Test')->first();
    expect($project->slug)->not->toBeNull();
});

it('requires a name to store a project', function () {
    loginUser();
    $response = $this->post(route('projects.store'), ['name' => '']);
    $response->assertSessionHasErrors('name');
});

// ---------------------------------------------------------------------------
// Projects — Edit & Update
// ---------------------------------------------------------------------------

it('shows the edit form for own project', function () {
    $user    = loginUser();
    $project = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

    $response = $this->get(route('projects.edit', $project));
    $response->assertStatus(200);
});

it('updates a project', function () {
    $user    = loginUser();
    $project = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

    $response = $this->put(route('projects.update', $project), [
        'name'        => 'Updated Name',
        'slug'        => $project->slug,
        'description' => 'Updated desc',
    ]);

    $response->assertRedirect(route('projects.index'));
    $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
});

// ---------------------------------------------------------------------------
// Projects — Destroy
// ---------------------------------------------------------------------------

it('soft-deletes a project', function () {
    $user    = loginUser();
    $project = Project::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertRedirect(route('projects.index'));
    $this->assertSoftDeleted('projects', ['id' => $project->id]);
});
