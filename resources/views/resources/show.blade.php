@extends('layouts.app')

@section('title', ucfirst(Str::singular($schema['name'])) . ' Details — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route($schema['base_route'].'.index') }}">{{ ucfirst($schema['name']) }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ $item->name ?? $item->id }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-document-text class="icon-svg" /></span>
            {{ ucfirst(Str::singular($schema['name'])) }} Details
        </h1>
        <div class="page-header-actions">
            <a class="btn-secondary" href="{{ route($schema['base_route'].'.index') }}">
                <x-heroicon-o-arrow-left class="btn-icon-svg" /> Back to list
            </a>
            <a class="btn-primary" href="{{ route($schema['base_route'].'.edit', $item) }}">
                <x-heroicon-o-pencil-square class="btn-icon-svg" /> Edit
            </a>
        </div>
    </div>

    <div class="section-card">
        <div class="detail-list">
            @foreach($schema['columns'] as $column)
            <div class="detail-row">
                <dt class="detail-label">@lang($column['name'])</dt>
                <dd class="detail-value">
                    @if(($column['type'] ?? null) === 'url')
                        <a href="{{ $item->{$column['name']} }}" target="_blank" class="detail-link">
                            {{ $item->{$column['name']} }}
                            <x-heroicon-o-arrow-top-right-on-square class="detail-link-icon" />
                        </a>
                    @elseif(is_array($item->{$column['name']}))
                        <pre class="detail-pre">{{ json_encode($item->{$column['name']}, JSON_PRETTY_PRINT) }}</pre>
                    @else
                        {{ $item->{$column['name']} }}
                    @endif
                </dd>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection