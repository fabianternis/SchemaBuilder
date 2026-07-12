<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
class ProjectController extends Controller
{
    public function index()
    {
        $schema = (new Project())->schema;
        $items = Project::all();

        return view('resources.index', compact('schema', 'items'));
    }

    public function create() { return view('projects.create'); }
    public function store(Request $request) { return redirect()->route('projects.index'); }
    public function show(string $id) { return view('projects.show', compact('id')); }
    public function edit(string $id) { return view('projects.edit', compact('id')); }
    public function update(Request $request, string $id) { return redirect()->route('projects.index'); }
    public function destroy(string $id) { return redirect()->route('projects.index'); }
}
