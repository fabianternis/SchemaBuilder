@extends('layouts.app')

@section('content')
<h1>Login</h1>
<form action="{{ route('auth.login') }}" method="POST">
    @csrf
    <input name="email" type="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password">
    <button>Login</button>
</form>
@endsection