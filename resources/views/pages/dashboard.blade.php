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


    <div class="section-card">
        <div class="section-title">Quick Start</div>
        <p style="margin-bottom: 1rem;">Create your first database schema or navigate to your projects to continue working.</p>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="{{ route('new') }}" class="btn-primary">
                <x-heroicon-o-plus class="btn-icon-svg" /> New Database
            </a>
            <a href="{{ route('projects.index') }}" class="btn-secondary">
                <x-heroicon-o-folder class="btn-icon-svg" /> My Projects
            </a>
        </div>
    </div>
</div>
@endsection