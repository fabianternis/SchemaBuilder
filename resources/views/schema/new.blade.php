@extends('layouts.app')

@section('title', 'New Database — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>New Database</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-plus-circle class="icon-svg" /></span>
            Create Database
        </h1>
    </div>

    <div class="section-card">
        <form action="{{ route('schema.storeNew') }}" method="post" class="form-stack">
            @csrf

            <div class="form-group">
                <label for="project">Project</label>
                <select name="project" id="project">
                    <option value="create_new">-- Create New --</option>
                    @foreach($projects as $listProject)
                        <option value="{{ $listProject->id }}" @selected(isset($project) && $project->id === $listProject->id)>{{ $listProject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="project_name">Project Name <span class="form-hint">(only if creating new project)</span></label>
                <input type="text" name="project_name" id="project_name" placeholder="My Project">
            </div>

            <div class="form-group">
                <label for="name">Database Name</label>
                <input type="text" name="name" id="name" class="mono" placeholder="my_project_db" required>
            </div>

            <div class="form-group">
                <label for="displayname">Display Name <span class="form-hint">(optional)</span></label>
                <input type="text" name="displayname" id="displayname" placeholder="My Project's DB">
            </div>

            <div class="form-group">
                <label for="description">Description <span class="form-hint">(optional)</span></label>
                <textarea name="description" id="description" rows="3" placeholder="Describe the purpose of this database..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-plus class="btn-icon-svg" /> Create Database
                </button>
            </div>
        </form>
    </div>

</div>
@endsection