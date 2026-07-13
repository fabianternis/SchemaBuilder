@extends('layouts.app')

@section('content')
<h1>Database: {{ $database->name }}</h1>
<p>Project: <a href="{{ route('schema.project', ['project' => $project->slug]) }}">{{ $project->name }}</a></p>
<hr>

@if($database->tables->isNotEmpty())
    <ul>
        @foreach($database->tables as $table)
            <li><a href="{{ route('schema.table', [$project, $database, $table]) }}">{{ $table->name }} (Table)</a></li>
        @endforeach
    </ul>
@else
    <p>No tables in this DB.</p>
@endif

{{-- <a href="{{ route('schema.table.create', [$project, $database]) }}">Create Teable</a> --}}
<form action="{{ route('schema.table.store', [$project, $database]) }}" method="post">
@csrf

    <label for="name">Table Name</label>
    <input type="text" name="name">
    <input type="submit" value="Create Table">
</form>
@endsection