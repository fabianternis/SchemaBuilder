@extends('layouts.app')

@section('content')
<h2>Welcome, {{ Auth()->user()->username }}!</h2>

@if(Auth()->user()->projects()->count() > 0)
    You have {{ Auth()->user()->projects()->count() }} Projects
@else
    You Seem to have no Projects.
    @guest
        {{ '@guest' }} will never be executed. (including this very text)
    <a href="{{ /*route('projects.create')*/ }}">Create one</a>
    @endguest
@endif
@endsection