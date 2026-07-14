@extends('layouts.app')

@section('title', 'Edit ' . ucfirst(Str::singular($schema['name'])) . ' — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route($schema['base_route'].'.index') }}">{{ ucfirst($schema['name']) }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route($schema['base_route'].'.show', $item) }}">{{ $item->name ?? $item->id }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>Edit</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-pencil-square class="icon-svg" /></span>
            Edit {{ ucfirst(Str::singular($schema['name'])) }}
        </h1>
        <div class="page-header-actions">
            <a class="btn-secondary" href="{{ route($schema['base_route'].'.index') }}">
                <x-heroicon-o-arrow-left class="btn-icon-svg" /> Back to list
            </a>
        </div>
    </div>

    <div class="section-card">
        <form action="{{ route($schema['base_route'].'.update', $item) }}" method="post" class="form-stack">
            @csrf
            @method('PUT')
            @foreach($schema['columns'] as $column)
            <div class="form-group">
                <label for="{{ $column['name'] }}">@lang($column['name'])<span class="form-hint">{{ !$column['required'] ? ' (Optional)' : '' }}</span></label>
                @if($column['type'] === 'textarea' || $column['type'] === 'json')
                    <textarea id="{{ $column['name'] }}" name="{{ $column['name'] }}" rows="3" {{ $column['required'] ? 'required' : '' }}>{{ old($column['name'], is_array($item->{$column['name']}) ? json_encode($item->{$column['name']}) : $item->{$column['name']}) }}</textarea>
                @else
                    <input id="{{ $column['name'] }}" type="{{ $column['type'] === 'url' ? 'url' : 'text' }}" name="{{ $column['name'] }}" value="{{ old($column['name'], $item->{$column['name']}) }}" {{ $column['required'] ? 'required' : '' }}>
                @endif
            </div>
            @endforeach
            <div class="form-actions">
                <a class="btn-ghost" href="{{ route($schema['base_route'].'.index') }}">
                    <x-heroicon-o-arrow-left class="btn-icon-svg" /> Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <x-heroicon-o-check class="btn-icon-svg" /> Update
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
