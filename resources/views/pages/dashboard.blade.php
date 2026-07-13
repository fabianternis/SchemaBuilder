@extends('layouts.app')

@section('content')
<h1>Dashboard</h1>
<a href="{{ route('projects.index') }}">Projects</a>
<form action="{{ route('auth.logout') }}" method="POST">
    @csrf
    <button>Logout</button>
</form>
@endsection