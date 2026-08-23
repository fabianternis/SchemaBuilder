@extends('layouts.app')

@section('content')
<div class="auth-container">
    <form action="{{ route('auth.signup') }}" method="POST">
        <h1>Signup</h1>
        @csrf
        <input name="username" type="text" placeholder="Username">
        <input name="email" type="email" placeholder="Email">
        <input name="password" type="password" placeholder="Password">
        <input type="submit" value="Sign up">
    </form>
    <a href="{{ route('auth.github') }}" class="btn-secondary">with GitHub</a><br><a class="btn-secondary" href="{{ route('auth.hackclub') }}">with HackClub</a>
</div>
@endsection