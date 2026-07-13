@extends('layouts.app')

@section('content')
<h1>Project: {{ $project->name }}</h1>
<a href="{{ route('projects.index') }}">Back to Projects</a>
<hr>

@if($project->databases->isNotEmpty())
    <ul>
        @foreach($project->databases as $database)
            <li>
                <a href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}">
                    {{ $database->name }}
                </a>
            </li>
        @endforeach
    </ul>
@else
    <p>No databases found for this project.</p>
@endif
@endsection