@extends('layouts.app')

@section('title', 'Project: ' . $project->name . ' — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ $project->name }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-folder class="icon-svg" /></span>
            {{ $project->name }}
        </h1>
        <div class="page-header-actions">
            <a class="btn-secondary" href="{{ route('projects.index') }}">
                <x-heroicon-o-arrow-left class="btn-icon-svg" /> Back to Projects
            </a>
        </div>
    </div>

    {{-- Databases List --}}
    <div class="section-card">
        <div class="section-title">Databases</div>
        @if($project->databases->isNotEmpty())
            <div class="list-group">
                @foreach($project->databases as $database)
                    <a href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}" class="list-group-item">
                        <x-heroicon-o-circle-stack class="list-item-icon" />
                        <span class="list-item-name">{{ $database->name }}</span>
                        <x-heroicon-o-chevron-right class="list-item-arrow" />
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><x-heroicon-o-circle-stack class="empty-icon-svg" /></div>
                <p>No databases found for this project.</p>
            </div>
        @endif
    </div>

</div>
@endsection