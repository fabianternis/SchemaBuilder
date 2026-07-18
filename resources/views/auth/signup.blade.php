@extends('layouts.app')

@section('content')
<form action="{{ route('auth.signup') }}" method="POST">
    <h1>Signup</h1>
    @csrf
    <input name="username" type="text" placeholder="Username">
    <input name="email" type="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password">
    <input type="submit" value="Sign up">
</form>
@endsection