@extends('layouts.app')

@section('title', 'Create Table — SchemaBuilder')

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
        <a href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}">{{ $database->name }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>Create Table</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-plus-circle class="icon-svg" /></span>
            Create Table
        </h1>
    </div>

    <div class="section-card">
        <form action="{{ route('schema.table.store', [$project, $database]) }}" method="post" class="form-stack">
            @csrf
            <div class="form-group">
                <label for="name">Table Name</label>
                <input type="text" name="name" id="name" class="mono" placeholder="e.g. users" required>
            </div>
            <div class="form-actions">
                <a class="btn-ghost" href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}">
                    <x-heroicon-o-arrow-left class="btn-icon-svg" /> Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-plus class="btn-icon-svg" /> Create Table
                </button>
            </div>
        </form>
    </div>

</div>
@endsection