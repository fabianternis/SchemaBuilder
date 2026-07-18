@extends('layouts.app')

@section('content')
<form action="{{ route('auth.login') }}" method="POST">
    <h1>Login</h1>
    @csrf
    <input name="email" type="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password">
    <input type="submit" value="Log in">
</form>
@endsection