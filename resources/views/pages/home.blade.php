@extends('layouts.app')

@section('content')
<h1>Home</h1>
@guest
<a href="{{ route('auth.login') }}">Login</a>
<a href="{{ route('auth.signup') }}">Signup</a>
@endguest
@endsection