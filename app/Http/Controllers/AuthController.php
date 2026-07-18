<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // Standard credential auth
    // ──────────────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($data)) {
            $request->session()->regenerate();
            return redirect()->route('root'); // will redirect to dashboard ... (due to router-logic)
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showSignup()
    {
        return view('auth.signup');
    }

    public function signup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'    => 'required|email|unique:users,email',
            'username' => 'string|required|max:255|unique:users,username',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'email'    => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('root');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GitHub OAuth
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Redirect the user to GitHub for authorization.
     */
    public function redirectToGitHub(): RedirectResponse
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle the callback from GitHub.
     *
     * Strategy:
     *   1. Look up an existing user by their github_id.
     *   2. If not found, try to match by e-mail (account merge / first OAuth login).
     *   3. If still not found, create a new account (no password — OAuth-only).
     */
    public function handleGitHubCallback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('auth.login')
                ->withErrors(['oauth' => 'GitHub authorization was denied or cancelled.']);
        }

        try {
            $socialUser = Socialite::driver('github')->user();
        } catch (\Exception $e) {
            return redirect()->route('auth.login')
                ->withErrors(['oauth' => 'Could not retrieve your GitHub profile. Please try again.']);
        }

        $user = User::where('github_id', $socialUser->getId())->first()
            ?? User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Refresh the stored token on every login
            $user->update([
                'github_id'            => $socialUser->getId(),
                'github_token'         => $socialUser->token,
                'github_refresh_token' => $socialUser->refreshToken,
            ]);
        } else {
            $user = User::create([
                'username'             => $this->uniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
                'email'                => $socialUser->getEmail(),
                'password'             => null,   // OAuth-only account
                'github_id'            => $socialUser->getId(),
                'github_token'         => $socialUser->token,
                'github_refresh_token' => $socialUser->refreshToken,
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('root');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HackClub OAuth
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Redirect the user to HackClub Auth for authorization.
     */
    public function redirectToHackClub(): RedirectResponse
    {
        return Socialite::driver('hackclub')->redirect();
    }

    /**
     * Handle the callback from HackClub Auth.
     *
     * Strategy mirrors the GitHub handler:
     *   1. Match by hackclub_id.
     *   2. Fall back to e-mail match (account linking).
     *   3. Create a new account if no match.
     */
    public function handleHackClubCallback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('auth.login')
                ->withErrors(['oauth' => 'HackClub authorization was denied or cancelled.']);
        }

        try {
            $socialUser = Socialite::driver('hackclub')->user();
        } catch (\Exception $e) {
            return redirect()->route('auth.login')
                ->withErrors(['oauth' => 'Could not retrieve your HackClub profile. Please try again.']);
        }

        $user = User::where('hackclub_id', $socialUser->getId())->first()
            ?? User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            $user->update([
                'hackclub_id'    => $socialUser->getId(),
                'hackclub_token' => $socialUser->token,
            ]);
        } else {
            $user = User::create([
                'username'       => $this->uniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
                'email'          => $socialUser->getEmail(),
                'password'       => null,   // OAuth-only account
                'hackclub_id'    => $socialUser->getId(),
                'hackclub_token' => $socialUser->token,
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->route('root');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Derive a unique username from an OAuth display name / nickname.
     * Appends a random 4-char suffix on collision to avoid uniqueness failures.
     */
    private function uniqueUsername(?string $base): string
    {
        $base = Str::slug($base ?? 'user', '_');
        $base = $base ?: 'user';
        $base = substr($base, 0, 250);   // leave room for the suffix

        $candidate = $base;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . '_' . Str::lower(Str::random(4));
        }

        return $candidate;
    }
}
