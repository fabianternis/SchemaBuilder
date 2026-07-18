# SchemaBuilder — OAuth: GitHub & HackClub

**Date:** 2026-07-18 19:50  
**Model:** Claude Sonnet 4.6 (Thinking)  
**Task:** Add OAuth 2.0 login via GitHub and HackClub Auth to the existing credential-based auth system

**Test result (before & after):** 48 tests / 103 assertions — all passing ✅

---

## Summary

Laravel Socialite (`v5.28`) was installed and wired into SchemaBuilder's auth system.
Two OAuth providers are now available alongside the existing email/password login:

| Provider | Driver key | Auth base URL |
|---|---|---|
| **GitHub** | `github` | `https://github.com/login/oauth` |
| **HackClub** | `hackclub` | `https://auth.hackclub.com/oauth` |

Both providers follow the same **find-or-create** strategy:
1. Look up existing user by provider ID (`github_id` / `hackclub_id`).
2. Fall back to email match — enabling account linking when the user signs up with the same address through different providers.
3. If no match, create a new account with `password = null` (OAuth-only).
4. Refresh the stored OAuth token on every successful login.

---

## New Routes

All four OAuth routes were added inside the existing `guest` middleware group
(users already logged in are redirected away by the middleware before they reach them).

```
GET /auth/github               auth.github           → redirectToGitHub()
GET /auth/github/callback      auth.github.callback  → handleGitHubCallback()

GET /auth/hackclub             auth.hackclub         → redirectToHackClub()
GET /auth/hackclub/callback    auth.hackclub.callback → handleHackClubCallback()
```

The callback URIs are also the defaults for `GITHUB_REDIRECT_URI` / `HACKCLUB_REDIRECT_URI`
in `config/services.php`, so they work out-of-the-box for local development.

---

## Files Changed

| File | Change |
|---|---|
| `composer.json` | **New dep:** `laravel/socialite ^5.28` |
| `app/Services/Socialite/HackClubProvider.php` | **New:** Custom Socialite driver for HackClub Auth |
| `app/Providers/AppServiceProvider.php` | **Updated:** Registers `hackclub` driver via `SocialiteFactory::extend()` |
| `app/Http/Controllers/AuthController.php` | **Updated:** Added 4 OAuth methods + `uniqueUsername()` helper |
| `routes/web.php` | **Updated:** 4 new OAuth routes inside `guest` group |
| `config/services.php` | **Updated:** `github` and `hackclub` service credential blocks |
| `database/migrations/2026_07_18_175233_add_oauth_columns_to_users_table.php` | **New:** Adds `hackclub_id`, `hackclub_token` nullable columns to `users` |
| `app/Models/User.php` | **Updated:** `hackclub_id`, `hackclub_token` added to `#[Fillable]` and `#[Hidden]` |
| `.env.example` | **Updated:** OAuth env-var stubs with registration instructions |

---

## Implementation Details

### HackClub OAuth Provider (`app/Services/Socialite/HackClubProvider.php`)

HackClub Auth is a standard OAuth 2.0 server hosted at `https://auth.hackclub.com`.
There is no community Socialite package for it, so a custom `AbstractProvider` subclass was written.

| Endpoint | URL |
|---|---|
| Authorization | `https://auth.hackclub.com/oauth/authorize` |
| Token exchange | `https://auth.hackclub.com/oauth/token` |
| User info | `https://auth.hackclub.com/api/v1/me` |

Default scope: `profile` (space-separated; matches HackClub's OAuth spec).

The provider maps the `/api/v1/me` response to Socialite's standard `User` object:
- `id`       → `$user['id']`
- `nickname` → `$user['username']` or `$user['slack_id']`
- `name`     → `$user['name']`
- `email`    → `$user['email']`
- `avatar`   → `$user['avatar']`

### AppServiceProvider

The driver is registered via the `SocialiteFactory` contract (resolved from the service container),
rather than the `Socialite` facade, to avoid alias resolution issues during `artisan` bootstrapping.

```php
$this->app->make(SocialiteFactory::class)->extend('hackclub', function ($app) {
    $config = $app['config']['services.hackclub'];
    return $app->make(SocialiteFactory::class)->buildProvider(HackClubProvider::class, $config);
});
```

### Database

The `users` table already had `github_id`, `github_token`, `github_refresh_token` from the
initial migration. This session adds only the missing HackClub columns:

```
hackclub_id     varchar nullable
hackclub_token  varchar nullable
```

Both columns are marked `hidden` in the User model (never serialized to JSON).

### `uniqueUsername()` Helper

OAuth providers supply a display name / nickname that may collide with existing usernames.
The private helper `uniqueUsername(string $base)` slugifies the name and appends a random
4-character suffix in a loop until the candidate is unique:

```php
private function uniqueUsername(?string $base): string
{
    $base      = Str::slug($base ?? 'user', '_') ?: 'user';
    $candidate = substr($base, 0, 250);  // leave room for suffix

    while (User::where('username', $candidate)->exists()) {
        $candidate = $base . '_' . Str::lower(Str::random(4));
    }

    return $candidate;
}
```

### Error Handling

Both callback handlers check for an `error` query parameter (sent by the provider when the
user denies access) and catch all `\Exception`s from Socialite, redirecting back to the
login page with a flash error message in both cases.

---

## Setup Instructions (for new installs)

### GitHub OAuth App

1. Go to **https://github.com/settings/applications/new**
2. Set **Authorization callback URL** to `http://localhost/auth/github/callback` (adjust for production)
3. Copy `Client ID` → `GITHUB_CLIENT_ID`
4. Generate a secret → `GITHUB_CLIENT_SECRET`
5. Leave `GITHUB_REDIRECT_URI` empty (the default callback path is used automatically)

### HackClub OAuth App

1. Sign in at **https://auth.hackclub.com**
2. Enable **Developer Mode** in your account settings
3. Navigate to **My OAuth Apps** and create a new app
4. Set **Redirect URI** to `http://localhost/auth/hackclub/callback`
5. Copy `Client ID` → `HACKCLUB_CLIENT_ID`
6. Copy `Client Secret` → `HACKCLUB_CLIENT_SECRET`

---

## Test Results

```
Tests:      48 passed   (unchanged — no existing tests broken)
Assertions: 103 passed
Duration:   ~808ms
```

> [!NOTE]
> The new OAuth callback handlers are not covered by the automated test suite because
> they depend on an external HTTP call to the provider. Integration tests would require
> mocking `Socialite::driver()` — recommended as a follow-up task.

---

## Recommended Next Steps

1. **Add OAuth buttons to login/signup views** — link to `route('auth.github')` and `route('auth.hackclub')`
2. **Mock & test OAuth callbacks** — use `Socialite::shouldReceive()` in Pest
3. **Encrypt stored OAuth tokens** — add `'github_token' => 'encrypted'` and `'hackclub_token' => 'encrypted'` to `User::casts()`
4. **Handle token revocation** — add a `DELETE /auth/connections/{provider}` route that nulls out the provider columns
5. **Account settings page** — show which OAuth providers are connected, allow connecting/disconnecting
