<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Project, SchemaDatabase as Database, SchemaTable as Table, SchemaColumn as Column};
// use Illuminate\Auth;
use Illuminate\Support\Str;
class SchemaController extends Controller
{

    public function store() {

    }
    public function index() {
        return view('schema.index');
    }

    public function showProject(Project $project) {
        // $project = Project::where('slug', $project_slug)->where('owner_id', auth()->id())->firstOrFail();
        // if(!isset($project)){
        //     abort(404, 'No Project could be Found');
        // } else {
        //     // $databases = SchemDatabase::where
        //     $databases = $project->databases();
        // }
        // return view('schema.project', compact(['project_slug', 'project']));

        abort_if($project->owner_id !== auth()->id(), 403);

        // $databases = $project->databases();
        $project->load('databases');
        return view('schema.project', compact(['project'/*, 'databases'*/]));
    }

    public function showDatabase(string $project_slug, string $database_name) {
        $project = Project::where('slug', $project_slug)->where('owner_id', Auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        return view('schema.database', compact(['project', 'database']));
    }

    public function showTable(string $project_slug, string $database_name, string $table_name) {
        $project = Project::where('slug', $project_slug)->where('owner_id', Auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        $table = Table::where('name', $table_name)->where('database_id', $database->id)->firstOrFail();
        return view('schema.table', compact('project', 'database', 'table'));
    }

    public function showColumn(string $project_slug, string $database_name, string $table_name, string $column_name) {
        $project = Project::where('slug', $project_slug)->where('owner_id', Auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        $table = Table::where('name', $table_name)->where('database_id', $database->id)->firstOrFail();
        $column = Column::where('name', $column_name)->where('table_id', $table->id)->firstOrFail();
        return view('schema.column', compact('project', 'database', 'table', 'column'));
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
