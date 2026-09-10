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

    {{-- Projects Section --}}
    <div class="section-card dashboard-projects-section">
        <div class="section-title-flex section-title">
            <span>Projects</span>
            @if($projects->isNotEmpty())
                <a href="{{ route('projects.create') }}" class="btn-secondary btn-secondary-sm">
                    <x-heroicon-o-plus class="btn-icon-svg" /> New Project
                </a>
            @endif
        </div>

        @if($projects->isNotEmpty())
            <div class="dashboard-projects-list">
                @foreach($projects as $project)
                    <div class="dashboard-project-item">
                        <div class="dashboard-project-header">
                            <div class="dashboard-project-title-group">
                                <a href="{{ route('schema.project', $project) }}" class="project-name">
                                    <x-heroicon-o-folder class="dashboard-project-icon" />
                                    <span>{{ $project->name }}</span>
                                </a>
                                <span class="dashboard-badge">
                                    {{ $project->databases->count() }} {{ Str::plural('database', $project->databases->count()) }}
                                </span>
                            </div>
                            <div class="dashboard-project-actions">
                                <a href="{{ route('new', $project->slug) }}" class="btn-secondary btn-secondary-sm" title="Add Database">
                                    <x-heroicon-o-plus class="btn-icon-svg" /> Add Database
                                </a>
                                <a href="{{ route('schema.project', $project) }}" class="btn-icon" title="View Project">
                                    <x-heroicon-o-arrow-right class="btn-icon-svg" />
                                </a>
                            </div>
                        </div>

                        @if($project->description)
                            <p class="dashboard-project-desc">{{ $project->description }}</p>
                        @endif

                        <div class="dashboard-project-databases-list">
                            @forelse($project->databases as $database)
                                <a href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}" class="dashboard-project-database-item">
                                    <x-heroicon-o-circle-stack class="database-card-icon" />
                                    <span class="database-name mono">{{ $database->name }}</span>
                                    <x-heroicon-o-chevron-right class="database-card-arrow" />
                                </a>
                            @empty
                                <div class="dashboard-databases-empty">
                                    <span>No databases in this project yet.</span>
                                    <a href="{{ route('new', $project->slug) }}" class="dashboard-databases-empty-link">
                                        <x-heroicon-o-plus class="btn-icon-svg" /> Create one
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon">
                    <x-heroicon-o-folder class="empty-icon-svg" />
                </div>
                <p>Seems like you have no projects yet.</p>
                <a href="{{ route('projects.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="btn-icon-svg" /> Create one
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
