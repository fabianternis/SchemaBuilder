<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        // $user = User::where('email', $data['email']);

        if(Auth::attempt($data)) {
            return redirect()->route('root'); // will redirect to dashboard ... (due to router-logic)
        }

        $request->session()->regenerate();

        return back(); // may set session-errors or sth.
    }

    public function showSignup() {
        return view('auth.signup');
    }

    public function signup(Request $request) {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'string|required|max:255|unique:users,username',
            'password' => 'required',
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
    }
}
