<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SchemaController extends Controller
{
    public function index() {
        return view('schema.index');
    }

    public function showProject(string $project_slug) {
        return view('schema.project', compact('project_slug'));
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
}
