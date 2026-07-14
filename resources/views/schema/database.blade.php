@extends('layouts.app')

@section('title', 'Database: ' . $database->name . ' — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('schema.project', ['project' => $project->slug]) }}">{{ $project->name }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ $database->name }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-circle-stack class="icon-svg" /></span>
            {{ $database->name }}
        </h1>
        <div class="page-header-actions">
            <a class="btn-secondary" href="{{ route('schema.project', ['project' => $project->slug]) }}">
                <x-heroicon-o-arrow-left class="btn-icon-svg" /> Back to Project
            </a>
        </div>
    </div>

    {{-- Tables List --}}
    <div class="section-card">
        <div class="section-title">Tables</div>
        @if($database->tables->isNotEmpty())
            <div class="list-group">
                @foreach($database->tables as $table)
                    <a href="{{ route('schema.table', [$project, $database, $table]) }}" class="list-group-item">
                        <x-heroicon-o-table-cells class="list-item-icon" />
                        <span class="list-item-name">{{ $table->name }}</span>
                        <x-heroicon-o-chevron-right class="list-item-arrow" />
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><x-heroicon-o-table-cells class="empty-icon-svg" /></div>
                <p>No tables in this database yet.</p>
            </div>
        @endif
    </div>

    {{-- Create Table --}}
    <div class="section-card">
        <div class="section-title">Create New Table</div>
        <form action="{{ route('schema.table.store', [$project, $database]) }}" method="post" class="form-inline">
            @csrf
            <div class="form-group form-group-inline">
                <label for="name">Table Name</label>
                <input type="text" name="name" id="name" class="mono" placeholder="e.g. users" required>
            </div>
            <button type="submit" class="btn-primary">
                <x-heroicon-o-plus class="btn-icon-svg" /> Create Table
            </button>
        </form>
    </div>

</div>
@endsection