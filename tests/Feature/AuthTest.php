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

it('redirects guests from dashboard', function () {
    $response = $this->get(route('pages.dashboard'));
    // Auth middleware redirects to the named route 'login' (which is /auth)
    $response->assertRedirect();
});

it('shows home page for guests', function () {
    $response = $this->get(route('pages.home'));
    $response->assertStatus(200);
});
