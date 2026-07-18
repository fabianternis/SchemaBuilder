<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\{Auth, Hash};

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request) {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(Auth::attempt($data)) {
            $request->session()->regenerate();
            return redirect()->route('root'); // will redirect to dashboard ... (due to router-logic)
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showSignup() {
        return view('auth.signup');
    }

    public function signup(Request $request) {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'string|required|max:255|unique:users,username',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);

        /*// Auth::login($user); // user should re-enter credentials*/
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('root');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
