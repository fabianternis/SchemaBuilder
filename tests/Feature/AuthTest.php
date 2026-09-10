<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// ---------------------------------------------------------------------------
// Authentication — Login
// ---------------------------------------------------------------------------

it('shows the login page for guests', function () {
    $response = $this->get(route('auth.login'));
    $response->assertStatus(200);
});

it('redirects authenticated users away from login page', function () {
    loginUser();
    $response = $this->get(route('auth.login'));
    $response->assertRedirect(); // guest middleware kicks in
});

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'secret123',
    ]);

    $response->assertRedirect(route('root'));
    $this->assertAuthenticatedAs($user);
});

it('fails login with wrong password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct'),
    ]);

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('fails login with missing fields', function () {
    $response = $this->post('/login', []);
    $response->assertSessionHasErrors(['email', 'password']);
});

// ---------------------------------------------------------------------------
// Authentication — Signup
// ---------------------------------------------------------------------------

it('shows the signup page for guests', function () {
    $response = $this->get(route('auth.signup'));
    $response->assertStatus(200);
});

it('registers a new user and logs them in', function () {
    $response = $this->post('/signup', [
        'username' => 'johndoe',
        'email'    => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('root'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'username' => 'johndoe']);
});

it('fails signup with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post('/signup', [
        'username' => 'newuser',
        'email'    => 'taken@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('fails signup with duplicate username', function () {
    User::factory()->create(['username' => 'taken']);

    $response = $this->post('/signup', [
        'username' => 'taken',
        'email'    => 'unique@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('username');
});

// ---------------------------------------------------------------------------
// Authentication — Logout
// ---------------------------------------------------------------------------

it('logs out an authenticated user and redirects to login', function () {
    loginUser();

    $response = $this->post(route('auth.logout'));

    $response->assertRedirect(route('auth.login'));
    $this->assertGuest();
});

it('requires auth for logout', function () {
    $response = $this->post(route('auth.logout'));
    // The auth middleware should redirect guests
    $response->assertRedirect();
});

// ---------------------------------------------------------------------------
// Pages — Dashboard
// ---------------------------------------------------------------------------

it('shows dashboard for authenticated user', function () {
    loginUser();
    $response = $this->get(route('pages.dashboard'));
    $response->assertStatus(200);
});

it('displays empty state on dashboard when user has no projects', function () {
    loginUser();
    $response = $this->get(route('pages.dashboard'));
    $response->assertStatus(200);
    $response->assertSee('Seems like you have no projects yet.');
    $response->assertSee(route('projects.create'));
});

it('displays projects and databases on dashboard', function () {
    $user = loginUser();

    $project = \App\Models\Project::create([
        'owner_id' => $user->id,
        'owner_type' => $user->getMorphClass(),
        'name' => 'Alpha Project',
        'slug' => 'alpha-project',
        'description' => 'First test project',
    ]);

    $database = \App\Models\SchemaDatabase::create([
        'project_id' => $project->id,
        'name' => 'alpha_db',
    ]);

    $response = $this->get(route('pages.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Alpha Project');
    $response->assertSee('First test project');
    $response->assertSee('alpha_db');
    $response->assertSee('1 database');
    $response->assertSee(route('schema.project', $project));
    $response->assertSee(route('schema.database', ['project' => $project->slug, 'database' => $database->name]));
    $response->assertSee(route('new', $project->slug));
});

it('displays empty database notice when project has no databases', function () {
    $user = loginUser();

    $project = \App\Models\Project::create([
        'owner_id' => $user->id,
        'owner_type' => $user->getMorphClass(),
        'name' => 'Empty DB Project',
        'slug' => 'empty-db-project',
    ]);

    $response = $this->get(route('pages.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Empty DB Project');
    $response->assertSee('0 databases');
    $response->assertSee('No databases in this project yet.');
    $response->assertSee(route('new', $project->slug));
});

it('redirects guests from dashboard', function () {
    $response = $this->get(route('pages.dashboard'));
    // Auth middleware redirects to the named route 'login' (which is /auth)
    $response->assertRedirect();
});


it('shows home page for guests', function () {
    $response = $this->get(route('pages.home'));
    $response->assertStatus(200);
});

// ---------------------------------------------------------------------------
// Authentication — OAuth (ENABLE_OAUTH)
// ---------------------------------------------------------------------------

it('does not display OAuth buttons when ENABLE_OAUTH is false', function () {
    config(['services.oauth.enabled' => false]);

    $loginResponse = $this->get(route('auth.login'));
    $loginResponse->assertStatus(200);
    $loginResponse->assertDontSee('with GitHub');
    $loginResponse->assertDontSee('with HackClub');

    $signupResponse = $this->get(route('auth.signup'));
    $signupResponse->assertStatus(200);
    $signupResponse->assertDontSee('with GitHub');
    $signupResponse->assertDontSee('with HackClub');
});

it('returns 404 on OAuth routes when ENABLE_OAUTH is false', function () {
    config(['services.oauth.enabled' => false]);

    $this->get(route('auth.github'))->assertStatus(404);
    $this->get(route('auth.github.callback'))->assertStatus(404);
    $this->get(route('auth.hackclub'))->assertStatus(404);
    $this->get(route('auth.hackclub.callback'))->assertStatus(404);
});

it('displays OAuth buttons on login and signup when ENABLE_OAUTH is true', function () {
    config(['services.oauth.enabled' => true]);

    $loginResponse = $this->get(route('auth.login'));
    $loginResponse->assertStatus(200);
    $loginResponse->assertSee('with GitHub');
    $loginResponse->assertSee('with HackClub');

    $signupResponse = $this->get(route('auth.signup'));
    $signupResponse->assertStatus(200);
    $signupResponse->assertSee('with GitHub');
    $signupResponse->assertSee('with HackClub');
});

it('initiates OAuth redirect when ENABLE_OAUTH is true', function () {
    config([
        'services.oauth.enabled' => true,
        'services.github.client_id' => 'test-github-id',
        'services.github.client_secret' => 'test-github-secret',
        'services.github.redirect' => 'http://localhost/auth/github/callback',
        'services.hackclub.client_id' => 'test-hackclub-id',
        'services.hackclub.client_secret' => 'test-hackclub-secret',
        'services.hackclub.redirect' => 'http://localhost/auth/hackclub/callback',
    ]);

    $githubResponse = $this->get(route('auth.github'));
    $githubResponse->assertRedirect();
    expect($githubResponse->headers->get('Location'))->toContain('github.com/login/oauth/authorize');

    $hackclubResponse = $this->get(route('auth.hackclub'));
    $hackclubResponse->assertRedirect();
    expect($hackclubResponse->headers->get('Location'))->toContain('auth.hackclub.com/oauth/authorize');
});
