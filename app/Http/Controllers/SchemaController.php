<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Project, SchemaDatabase};
use Illuminate\Auth;
use Illuminate\Support\Str;
class SchemaController extends Controller
{

    public function store() {

    }
    public function index() {
        return view('schema.index');
    }

    public function showProject(string $project_slug) {
        $project = Project::where('slug', $project_slug)->where('owner_id', auth()->id())->firstOrFail();
        if(!isset($project)){
            abort(404, 'No Project could be Found');
        } else {
            // $databases = SchemDatabase::where
            $databases = $project->databases();
        }
        return view('schema.project', compact(['project_slug', 'project']));
    }

    public function showDatabase(string $project_slug, string $database_name) {
        return view('schema.database', compact('project_slug', 'database_name'));
    }

    public function showTable(string $project_slug, string $database_name, string $table_name) {
        return view('schema.table', compact('project_slug', 'database_name', 'table_name'));
    }

    public function showColumn(string $project_slug, string $database_name, string $table_name, string $column_name) {
        return view('schema.column', compact('project_slug', 'database_name', 'table_name', 'column_name'));
    }




    public function quickCreate()
    {
        $projects = Project::where('owner_id', Auth()->id())->get();
        return view('schema.new', compact('projects'));
    }

public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'project' => ['required', 'string'],
            'project_name' => ['required_if:project,create_new', 'nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'displayname' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validated['project'] === 'create_new') {
            $project = Project::create([
                'owner_id' => auth()->id(),
                'owner_type' => get_class(auth()->user()), 
            // $project = new Project([
                'name' => $validated['project_name'],
                'slug' => Str::slug($validated['project_name']),
            ]);

            Auth()->user()->projects()->save($project);
        } else {
            $project = Project::where('id', $validated['project'])->where('owner_id', auth()->id())->firstOrFail();
        }

        $database = new SchemaDatabase([
            'name' => $validated['name'],
            'displayname' => $validated['displayname'],
            'description' => $validated['description'],
        ]);

        $project->databases()->save($database);

        return redirect()->route('schema.database', [
            'project_slug'  => $project->slug,
            'database_name' => $database->name,
        ]);
    }
}
