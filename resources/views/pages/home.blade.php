@extends('layouts.app')

@section('title', 'SchemaBuilder — Design your database schema')

@section('content')
<div class="page">

    {{-- Hero --}}
    <div class="home-hero">
        <div class="home-logo">SB</div>

        <h1 class="home-title">Design database schemas<br>without the fuss.</h1>

        <p class="home-sub">
            SchemaBuilder lets you visually design, manage and export database
            schemas — tables, columns, types, constraints and foreign keys, all in one place.
        </p>

        <div class="home-actions">
            <a class="btn-primary" href="{{ route('auth.signup') }}">
                <x-heroicon-o-user-plus class="btn-icon-svg" /> Get started
            </a>
            <a class="btn-secondary" href="{{ route('auth.login') }}">
                <x-heroicon-o-arrow-right-on-rectangle class="btn-icon-svg" /> Log in
            </a>
        </div>
    </div>

    {{-- Feature pills --}}
    <div class="home-features">
        <div class="home-feature">
            <span class="hf-icon"><x-heroicon-o-table-cells /></span>
            <span class="hf-title">Tables &amp; Columns</span>
            <span class="hf-desc">Add, reorder and configure columns with types, constraints and defaults.</span>
        </div>
        <div class="home-feature">
            <span class="hf-icon"><x-heroicon-o-link /></span>
            <span class="hf-title">Foreign Keys</span>
            <span class="hf-desc">Wire up relationships between tables with ON DELETE cascade rules.</span>
        </div>
        <div class="home-feature">
            <span class="hf-icon"><x-heroicon-o-arrow-down-tray /></span>
            <span class="hf-title">SQL Export</span>
            <span class="hf-desc">Export your schema as a ready-to-run CREATE TABLE SQL script.</span>
        </div>
        <div class="home-feature">
            <span class="hf-icon"><x-heroicon-o-folder /></span>
            <span class="hf-title">Projects</span>
            <span class="hf-desc">Organise multiple databases and schemas under separate projects.</span>
        </div>
    </div>

</div>
@endsection