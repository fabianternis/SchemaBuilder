@extends('layouts.app')

@section('content')
<div class="auth-container">
    <form action="{{ route('auth.login') }}" method="POST">
        <h1>Login</h1>
        @csrf
        <input name="email" type="email" placeholder="Email">
        <input name="password" type="password" placeholder="Password">
        <input type="submit" value="Log in">
    </form>
    <a class="btn-secondary" href="{{ route('auth.github') }}">with GitHub</a><br><a class="btn-secondary" href="{{ route('auth.hackclub') }}">with HackClub</a>
</div>
@endsection