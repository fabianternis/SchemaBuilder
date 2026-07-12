@extends('layouts.app')

@section('content')
<form action="{{ route('schema.storeNew') }}" method="post">
@csrf

    <label for="project">Project</label>
    <select name="project">
        <option value="create_new">-- Create New --</option>
        @foreach($projects as $project)
        <option value="{{ $project->id }}">{{ $project->name }}</option>
        @endforeach
    </select>
    <label for="name">Database Name</label>
    <input type="text" name="name" placeholder="my_project_db">
    <label for="displayname">Database Displayname (Optional)</label>
    <input type="text" name="displayname" placeholder="My Project's DB">
    <label for="description">Database Description (Optional)</label>
    <textarea name="description">Used for All audit-logs</textarea>
</form>
@endsection