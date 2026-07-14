@extends('layouts.app')

@section('title', 'Create ' . ucfirst(Str::singular($schema['name'])) . ' — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route($schema['base_route'].'.index') }}">{{ ucfirst($schema['name']) }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>Create</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-plus-circle class="icon-svg" /></span>
            Create {{ ucfirst(Str::singular($schema['name'])) }}
        </h1>
    </div>

    <div class="section-card">
        <form action="{{ route($schema['base_route'].'.store') }}" method="post" class="form-stack">
            @csrf
            @foreach($schema['columns'] as $column)
            <div class="form-group">
                <label for="{{ $column['name'] }}">@lang($column['name']){{ !$column['required'] ? '' : '' }}<span class="form-hint">{{ !$column['required'] ? ' (Optional)' : '' }}</span></label>
                @if($column['type'] === 'textarea')
                    <textarea id="{{ $column['name'] }}" name="{{ $column['name'] }}" rows="3" {{ $column['required'] ? 'required' : '' }}></textarea>
                @else
                    <input id="{{ $column['name'] }}" type="{{ $column['type'] === 'url' ? 'url' : 'text' }}" name="{{ $column['name'] }}" {{ $column['required'] ? 'required' : '' }}>
                @endif
            </div>
            @endforeach
            <div class="form-actions">
                <a class="btn-ghost" href="{{ route($schema['base_route'].'.index') }}">
                    <x-heroicon-o-arrow-left class="btn-icon-svg" /> Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-plus class="btn-icon-svg" /> Create
                </button>
            </div>
        </form>
    </div>

</div>
@endsection