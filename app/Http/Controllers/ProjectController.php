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

    public function create()
    {
        $schema = (new Project())->schema;

        return view('resources.create', compact('schema'));
    }
    private function validateSchema(Request $request, array $schema)
    {
        $rules = [];
        foreach ($schema['columns'] as $column) {
            $columnRules = [];
            if (isset($column['required']) && $column['required']) {
                $columnRules[] = 'required';
            } else {
                $columnRules[] = 'nullable';
            }
            if ($column['type'] === 'url') {
                $columnRules[] = 'url';
            } elseif ($column['type'] === 'json') {
                $columnRules[] = 'json';
            } elseif ($column['type'] === 'text' || $column['type'] === 'textarea') {
                $columnRules[] = 'string';
            }
            $rules[$column['name']] = $columnRules;
        }

        return $request->validate($rules);
    }

    public function store(Request $request)
    {
        $schema = (new Project())->schema;
        $validated = $this->validateSchema($request, $schema);
        
        $validated['owner_id'] = $request->user()->id;
        $validated['owner_type'] = $request->user()->getMorphClass();

        Project::create($validated);

        return redirect()->route($schema['base_route'] . '.index');
    }

    public function show(string $id)
    {
        $schema = (new Project())->schema;
        $item = Project::findOrFail($id);

        return view('resources.show', compact('schema', 'item'));
    }

    public function edit(string $id)
    {
        $schema = (new Project())->schema;
        $item = Project::findOrFail($id);

        return view('resources.edit', compact('schema', 'item'));
    }

    public function update(Request $request, string $id)
    {
        $schema = (new Project())->schema;
        $validated = $this->validateSchema($request, $schema);

        $item = Project::findOrFail($id);
        $item->update($validated);

        return redirect()->route($schema['base_route'] . '.index');
    }

    public function destroy(string $id)
    {
        $schema = (new Project())->schema;
        $item = Project::findOrFail($id);
        $item->delete();

        return redirect()->route($schema['base_route'] . '.index');
    }
}
