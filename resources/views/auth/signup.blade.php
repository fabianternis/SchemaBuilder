@extends('layouts.app')

@section('title', 'Signup — SchemaBuilder')

@section('content')
<div class="auth-wrap">
    <div class="auth-card auth-container">
        <h1>Signup</h1>

        @if ($errors->any())
            <div class="form-errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="auth-form" action="{{ route('auth.signup') }}" method="POST">
            @csrf
            <input name="username" type="text" placeholder="Username" value="{{ old('username') }}" required autofocus>
            <input name="email" type="email" placeholder="Email" value="{{ old('email') }}" required>
            <input name="password" type="password" placeholder="Password" required>
            <input type="submit" class="btn-primary" value="Sign up">
        </form>

        @if (config('services.oauth.enabled', false))
            <div class="oauth-divider"><span>or continue with</span></div>
            <div class="oauth-buttons">
                <a class="btn-secondary" href="{{ route('auth.github') }}">with GitHub</a>
                <a class="btn-secondary" href="{{ route('auth.hackclub') }}">with HackClub</a>
            </div>
        @endif

        <div class="auth-footer">
            Already have an account? <a href="{{ route('auth.login') }}">Log in</a>
        </div>
    </div>
</div>
@endsection