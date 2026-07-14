<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Project, SchemaDatabase as Database, SchemaTable as Table, SchemaColumn as Column};
use Illuminate\Support\Str;

class SchemaController extends Controller
{

    public function store() {

    }
    public function index() {
        return view('schema.index');
    }

    public function showProject(Project $project) {
        abort_if($project->owner_id !== auth()->id(), 403);

        $project->load('databases');
        return view('schema.project', compact(['project']));
    }

    public function showDatabase(string $project_slug, string $database_name) {
        $project = Project::where('slug', $project_slug)->where('owner_id', Auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        return view('schema.database', compact(['project', 'database']));
    }

    public function createTable(Project $project, Database $database)
    {
        return view('schema.table.create', compact([$project, $database]));
    }

    public function storeTable(Request $request, Project $project, Database $database)
    {
        $data = $request->validate([
            'name' => 'required|string',
        ]);


        $baseName = $data['name'];
        $name = $baseName;
        $counter = 1;

        while (Table::where('name', $name)->exists()) {
            $name = $baseName . '_' . $counter++;
        }

        $table = Table::create([
            'database_id' => $database->id,
            'name' => $data['name'],
        ]);

        return redirect()->route('schema.table', compact(['project', 'database', 'table']));
    }

    public function showTable(Project $project, Database $database, Table $table) {
        // $project  = Project::where('slug', $project_slug)->where('owner_id', Auth()->id())->firstOrFail();
        // $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        // $table    = Table::where('name', $table_name)->where('database_id', $database->id)->firstOrFail();

        // Eager-load columns with their referenced table info
        $table->load(['columns' => fn($q) => $q->orderBy('order_index')->orderBy('created_at'), 'columns.referencedTable']);

        // All tables in this database (for FK selection)
        $allTables = Table::where('database_id', $database->id)->get(['id', 'name']);

        return view('schema.table', compact('project', 'database', 'table', 'allTables'));
    }

    public function showColumn(Project $project, Database $database, Table $table, Column $column) {
        // $project  = Project::where('slug', $project_slug)->where('owner_id', Auth()->id())->firstOrFail();
        // $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        // $table    = Table::where('name', $table_name)->where('database_id', $database->id)->firstOrFail();
        // $column   = Column::where('name', $column_name)->where('table_id', $table->id)->firstOrFail();

        // Sibling columns (for context), ordered
        $table->load(['columns' => fn($q) => $q->orderBy('order_index')->orderBy('created_at')]);

        // All tables in database (for FK dialog)
        $allTables = Table::where('database_id', $database->id)->get(['id', 'name']);

        return view('schema.column', compact('project', 'database', 'table', 'column', 'allTables'));
    }

    // -------------------------------------------------------------------------
    // JSON API: update a table and its columns
    // -------------------------------------------------------------------------
    public function updateTable(Request $request, string $project_slug, string $database_name, string $table_name)
    {
        $project  = Project::where('slug', $project_slug)->where('owner_id', auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        $table    = Table::where('name', $table_name)->where('database_id', $database->id)->firstOrFail();

        $validated = $request->validate([
            'name'                       => ['required', 'string', 'max:255'],
            'columns'                    => ['present', 'array'],
            'columns.*.id'               => ['nullable', 'string'],
            'columns.*.name'             => ['required', 'string', 'max:255'],
            'columns.*.type'             => ['required', 'string', 'max:100'],
            'columns.*.is_nullable'      => ['boolean'],
            'columns.*.is_primary'       => ['boolean'],
            'columns.*.is_unique'        => ['boolean'],
            'columns.*.auto_increment'   => ['boolean'],
            'columns.*.default'          => ['nullable', 'string', 'max:255'],
            'columns.*.length'           => ['nullable', 'integer', 'min:1'],
            'columns.*.on_cascade'       => ['nullable', 'string', 'max:50'],
            'columns.*.referenced_table_id' => ['nullable', 'string'],
        ]);

        // Rename table if name changed
        if ($table->name !== $validated['name']) {
            $table->update(['name' => $validated['name']]);
        }

        // Sync columns
        $incomingIds = collect($validated['columns'])->pluck('id')->filter()->values();

        // Soft-delete columns not in the incoming list
        $table->columns()->whereNotIn('id', $incomingIds)->delete();

        $savedColumns = [];
        foreach ($validated['columns'] as $index => $colData) {
            $attrs = [
                'table_id'            => $table->id,
                'name'                => $colData['name'],
                'type'                => $colData['type'],
                'is_nullable'         => $colData['is_nullable']      ?? false,
                'is_primary'          => $colData['is_primary']       ?? false,
                'is_unique'           => $colData['is_unique']        ?? false,
                'auto_increment'      => $colData['auto_increment']   ?? false,
                'default'             => $colData['default']          ?? null,
                'length'              => $colData['length']           ?? null,
                'on_cascade'          => $colData['on_cascade']       ?? null,
                'referenced_table_id' => $colData['referenced_table_id'] ?? null,
                'order_index'         => $index,
            ];

            if (!empty($colData['id'])) {
                $col = Column::where('id', $colData['id'])->where('table_id', $table->id)->first();
                if ($col) {
                    $col->update($attrs);
                } else {
                    $col = Column::create($attrs);
                }
            } else {
                $col = Column::create($attrs);
            }
            $savedColumns[] = $col->fresh(['referencedTable']);
        }

        $table->load('columns.referencedTable');

        return response()->json([
            'success' => true,
            'message' => 'Table saved successfully.',
            'table'   => $table,
            'columns' => $savedColumns,
        ]);
    }

    // -------------------------------------------------------------------------
    // JSON API: update a single column
    // -------------------------------------------------------------------------
    public function updateColumn(Request $request, string $project_slug, string $database_name, string $table_name, string $column_name)
    {
        $project  = Project::where('slug', $project_slug)->where('owner_id', auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        $table    = Table::where('name', $table_name)->where('database_id', $database->id)->firstOrFail();
        $column   = Column::where('name', $column_name)->where('table_id', $table->id)->firstOrFail();

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'type'                => ['required', 'string', 'max:100'],
            'is_nullable'         => ['boolean'],
            'is_primary'          => ['boolean'],
            'is_unique'           => ['boolean'],
            'auto_increment'      => ['boolean'],
            'default'             => ['nullable', 'string', 'max:255'],
            'length'              => ['nullable', 'integer', 'min:1'],
            'on_cascade'          => ['nullable', 'string', 'max:50'],
            'referenced_table_id' => ['nullable', 'string'],
        ]);

        $column->update($validated);
        $column->load('referencedTable');

        return response()->json([
            'success' => true,
            'message' => 'Column saved successfully.',
            'column'  => $column,
        ]);
    }

    // -------------------------------------------------------------------------
    // JSON API: list all tables in a database (for FK selectors)
    // -------------------------------------------------------------------------
    public function tablesList(string $project_slug, string $database_name)
    {
        $project  = Project::where('slug', $project_slug)->where('owner_id', auth()->id())->firstOrFail();
        $database = Database::where('name', $database_name)->where('project_id', $project->id)->firstOrFail();
        $tables   = Table::where('database_id', $database->id)->get(['id', 'name']);

        return response()->json(['tables' => $tables]);
    }

    // -------------------------------------------------------------------------
    // Quick Create / Store (original)
    // -------------------------------------------------------------------------
    public function quickCreate(?Project $project)
    {
        $projects = Project::where('owner_id', Auth()->id())->get();
        return view('schema.new', compact(['project', 'projects']));
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'project'      => ['required', 'string'],
            'project_name' => ['required_if:project,create_new', 'nullable', 'string', 'max:255'],
            'name'         => ['required', 'string', 'max:255'],
            'displayname'  => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
        ]);

        if ($validated['project'] === 'create_new') {
            $baseSlug = Str::slug($validated['project_name']);
            $slug = $baseSlug;
            $counter = 1;

            while (Project::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $project = Project::create([
                'owner_id'   => auth()->id(),
                'owner_type' => get_class(auth()->user()),
                'name'       => $validated['project_name'],
                'slug'       => $slug,
            ]);
        } else {
            $project = Project::where('id', $validated['project'])->where('owner_id', auth()->id())->firstOrFail();
        }

        $database = new Database([
            'name'        => $validated['name'],
            'displayname' => $validated['displayname'],
            'description' => $validated['description'],
        ]);

        $project->databases()->save($database);

        return redirect()->route('schema.database', compact(['project', 'database']));
    }
}
