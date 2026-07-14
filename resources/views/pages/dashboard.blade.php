@extends('layouts.app')

@section('title', 'Dashboard — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <span>Dashboard</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-squares-2x2 class="icon-svg" /></span>
            Dashboard
        </h1>
        <div class="page-header-actions">
            <a class="btn-secondary" href="{{ route('projects.index') }}">
                <x-heroicon-o-folder class="btn-icon-svg" /> Projects
            </a>
            <form action="{{ route('auth.logout') }}" method="POST" class="inline-form">
                @csrf
                <button class="btn-secondary btn-danger-outline" type="submit">
                    <x-heroicon-o-arrow-right-on-rectangle class="btn-icon-svg" /> Logout
                </button>
            </form>
        </div>
    </div>

</div>
@endsection