@extends('layouts.app')

@section('title', ucfirst($schema['name']) . ' — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ ucfirst($schema['name']) }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-rectangle-stack class="icon-svg" /></span>
            {{ ucfirst($schema['name']) }}
        </h1>
        <div class="page-header-actions">
            <a class="btn-primary" href="{{ route($schema['base_route'].'.create') }}">
                <x-heroicon-o-plus class="btn-icon-svg" /> Create
            </a>
        </div>
    </div>

    @if(isset($items) && $items->count() > 0)
    <div class="section-card">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($schema['columns'] as $column)
                        @if($column['on_index'])
                            <th>{{ $column['name'] }}</th>
                        @endif
                    @endforeach
                    <th class="col-actions-header">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    @foreach($schema['columns'] as $column)
                        @if($column['on_index'])
                            @if($column['type'] == 'textarea')
                                <td>{{ Str::limit($item->{$column['name']}, 12) }}</td>
                            @else
                                <td>{{ $item->{$column['name']} }}</td>
                            @endif
                        @endif
                    @endforeach
                    <td class="col-actions-cell">
                        <a href="{{ route($schema['base_route'].'.show', $item) }}" class="btn-icon" title="View">
                            <x-heroicon-o-eye class="btn-icon-svg" />
                        </a>
                        <a href="{{ route($schema['base_route'].'.edit', $item) }}" class="btn-icon" title="Edit">
                            <x-heroicon-o-pencil-square class="btn-icon-svg" />
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="section-card">
        <div class="empty-state">
            <div class="empty-state-icon"><x-heroicon-o-inbox class="empty-icon-svg" /></div>
            <p>You don't have any {{ $schema['name'] }} yet.</p>
            <a class="btn-primary" href="{{ route($schema['base_route'].'.create') }}">
                <x-heroicon-o-plus class="btn-icon-svg" /> Create one
            </a>
        </div>
    </div>
    @endif

</div>
@endsection