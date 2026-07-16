<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Project;
class ProjectController extends Controller
{
    public function index()
    {
        $schema = (new Project())->schema;
        $items = Project::where('owner_id', auth()->id())->get();

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

        $validated['owner_id']   = $request->user()->id;
        $validated['owner_type'] = $request->user()->getMorphClass();

        // Auto-generate slug from name if not provided
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Project::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        Project::create($validated);

        return redirect()->route($schema['base_route'] . '.index');
    }

    public function show(Project $project)
    {
        $schema = (new Project())->schema;
        $item = $project;

        return view('resources.show', compact('schema', 'item'));
    }

    public function edit(Project $project)
    {
        $schema = (new Project())->schema;
        $item = $project;

        return view('resources.edit', compact('schema', 'item'));
    }

    public function update(Request $request, Project $project)
    {
        $schema = (new Project())->schema;
        $validated = $this->validateSchema($request, $schema);

        // Auto-generate slug from name if not provided
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Project::where('slug', $slug)->where('id', '!=', $project->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        $project->update($validated);

        return redirect()->route($schema['base_route'] . '.index');
    }

    public function destroy(Project $project)
    {
        $schema = (new Project())->schema;
        $project->delete();

        return redirect()->route($schema['base_route'] . '.index');
    }
}
