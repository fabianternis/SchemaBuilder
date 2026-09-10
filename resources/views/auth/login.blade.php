@extends('layouts.app')

@section('title', 'Login — SchemaBuilder')

@section('content')
<div class="auth-wrap">
    <div class="auth-card auth-container">
        <h1>Login</h1>

        @if ($errors->any())
            <div class="form-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="auth-form" action="{{ route('auth.login') }}" method="POST">
            @csrf
            <input name="email" type="email" placeholder="Email" value="{{ old('email') }}" required autofocus>
            <input name="password" type="password" placeholder="Password" required>
            <input type="submit" class="btn-primary" value="Log in">
        </form>

        @if (config('services.oauth.enabled', false))
            <div class="oauth-divider"><span>or continue with</span></div>
            <div class="oauth-buttons">
                <a class="btn-secondary" href="{{ route('auth.github') }}">with GitHub</a>
                <a class="btn-secondary" href="{{ route('auth.hackclub') }}">with HackClub</a>
            </div>
        @endif

        <div class="auth-footer">
            Don't have an account? <a href="{{ route('auth.signup') }}">Sign up</a>
        </div>
    </div>
</div>
@endsection