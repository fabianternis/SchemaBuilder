@extends('layouts.app')

@section('content')
<form action="{{ route('schema.storeNew') }}" method="post">
@csrf

    <label for="project">Project</label>
    <select name="project">
        <option value="create_new">-- Create New --</option>
        @foreach($projects as $listProject)
            <option value="{{ $listProject->id }}" @selected(isset($project) && $project->id === $listProject->id)>{{ $listProject->name }}</option>
        @endforeach
    </select>
    <label for="project_name">Project Name (only if no Project is selected, no JS yet)</label>
    <input type="text" name="project_name" placeholder="My Project">
    <label for="name">Database Name</label>
    <input type="text" name="name" placeholder="my_project_db">
    <label for="displayname">Database Displayname (Optional)</label>
    <input type="text" name="displayname" placeholder="My Project's DB">
    <label for="description">Database Description (Optional)</label>
    <textarea name="description">Used for All audit-logs</textarea>
    <input type="submit" value="create Database">
</form>
@endsection