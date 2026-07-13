@extends('layouts.app')

@section('content')
<h1>Signup</h1>
<form action="{{ route('auth.signup') }}" method="POST">
    @csrf
    <input name="username" placeholder="Username">
    <input name="email" type="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password">
    <button>Signup</button>
</form>
@endsection