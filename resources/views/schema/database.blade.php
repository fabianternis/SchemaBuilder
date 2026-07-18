@extends('layouts.app')

@section('title', 'Database: ' . $database->name . ' — SchemaBuilder')

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('schema.project', $project) }}">{{ $project->name }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ $database->name }}</span>
    </nav>

    {{-- Flash messages --}}
    @if(session('import_success'))
        <div class="flash flash-success" role="alert">
            <x-heroicon-o-check-circle class="flash-icon" />
            {{ session('import_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flash flash-error" role="alert">
            <x-heroicon-o-exclamation-circle class="flash-icon" />
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-circle-stack class="icon-svg" /></span>
            {{ $database->name }}
        </h1>
        <div class="page-header-actions">
            <a class="btn-secondary" href="{{ route('schema.project', $project) }}">
                <x-heroicon-o-arrow-left class="btn-icon-svg" /> Back to Project
            </a>

            {{-- Export dropdown --}}
            <div class="export-dropdown" id="export-dropdown">
                <button
                    class="btn-secondary export-dropdown-trigger"
                    type="button"
                    id="export-dropdown-btn"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="export-dropdown-menu"
                >
                    <x-heroicon-o-arrow-down-tray class="btn-icon-svg" />
                    Export
                    <x-heroicon-o-chevron-down class="btn-icon-svg export-chevron" />
                </button>
                <ul class="export-dropdown-menu" id="export-dropdown-menu" role="menu" aria-labelledby="export-dropdown-btn">
                    <li class="export-dropdown-label">Export as…</li>
                    <li role="menuitem">
                        <a href="{{ route('schema.export', [$project, $database, 'sql']) }}" class="export-dropdown-item">
                            <span class="export-icon export-icon-sql">SQL</span>
                            <span class="export-item-text">
                                <strong>SQL</strong>
                                <small>CREATE TABLE statements</small>
                            </span>
                        </a>
                    </li>
                    <li role="menuitem">
                        <a href="{{ route('schema.export', [$project, $database, 'laravel']) }}" class="export-dropdown-item">
                            <span class="export-icon export-icon-laravel">L</span>
                            <span class="export-item-text">
                                <strong>Laravel Migration</strong>
                                <small>Schema::create(…) files</small>
                            </span>
                        </a>
                    </li>
                    <li role="menuitem">
                        <a href="{{ route('schema.export', [$project, $database, 'json']) }}" class="export-dropdown-item">
                            <span class="export-icon export-icon-json">{}</span>
                            <span class="export-item-text">
                                <strong>JSON Schema</strong>
                                <small>Machine-readable schema</small>
                            </span>
                        </a>
                    </li>
                    <li role="menuitem">
                        <a href="{{ route('schema.export', [$project, $database, 'csv']) }}" class="export-dropdown-item">
                            <span class="export-icon export-icon-csv">CSV</span>
                            <span class="export-item-text">
                                <strong>CSV</strong>
                                <small>Column definitions spreadsheet</small>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            {{-- /Export dropdown --}}
        </div>
    </div>

    {{-- Tables List --}}
    <div class="section-card">
        <div class="section-title">Tables</div>
        @if($database->tables->isNotEmpty())
            <div class="list-group">
                @foreach($database->tables as $table)
                    <a href="{{ route('schema.table', [$project, $database, $table]) }}" class="list-group-item">
                        <x-heroicon-o-table-cells class="list-item-icon" />
                        <span class="list-item-name">{{ $table->name }}</span>
                        <x-heroicon-o-chevron-right class="list-item-arrow" />
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon"><x-heroicon-o-table-cells class="empty-icon-svg" /></div>
                <p>No tables in this database yet.</p>
            </div>
        @endif
    </div>

    {{-- Create Table --}}
    <div class="section-card">
        <div class="section-title">Create New Table</div>
        <form action="{{ route('schema.table.store', [$project, $database]) }}" method="post" class="form-inline">
            @csrf
            <div class="form-group form-group-inline">
                <label for="name">Table Name</label>
                <input type="text" name="name" id="name" class="mono" placeholder="e.g. users" required>
            </div>
            <button type="submit" class="btn-primary">
                <x-heroicon-o-plus class="btn-icon-svg" /> Create Table
            </button>
        </form>
    </div>

    {{-- Import Schema --}}
    <div class="section-card">
        <div class="section-title-flex section-title">
            <span>Import Schema</span>
        </div>
        <p class="import-description">
            Upload a schema file to add tables &amp; columns to this database.
            Existing tables with matching names will have columns merged (not deleted).
        </p>

        <form
            action="{{ route('schema.import', [$project, $database]) }}"
            method="post"
            enctype="multipart/form-data"
            class="import-form"
            id="import-form"
        >
            @csrf
            <div class="form-group">
                <label for="from">Import format</label>
                <select name="from" id="from" class="import-select">
                    <option value="sql">SQL — CREATE TABLE statements (.sql, .txt)</option>
                    <option value="json">JSON — SchemaBuilder export (.json)</option>
                    <option value="csv">CSV — Column definitions (.csv)</option>
                </select>
            </div>

            <div class="import-file-area" id="import-drop-zone">
                <input
                    type="file"
                    name="schema"
                    id="schema"
                    class="import-file-input"
                    accept=".sql,.txt,.json,.csv"
                    required
                >
                <div class="import-file-label" id="import-file-label">
                    <x-heroicon-o-arrow-up-tray class="import-upload-icon" />
                    <span class="import-file-label-text">
                        Drop a file here, or <span class="import-file-link">browse</span>
                    </span>
                    <small class="import-file-hint">Supported: .sql, .txt, .json, .csv &mdash; max 2 MB</small>
                </div>
                <div class="import-file-chosen" id="import-file-chosen" style="display:none;">
                    <x-heroicon-o-document class="import-chosen-icon" />
                    <span id="import-chosen-name"></span>
                    <button type="button" class="btn-icon danger import-clear-btn" id="import-clear-btn" title="Remove file">
                        <x-heroicon-o-x-mark class="btn-icon-svg" />
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" id="import-submit-btn" disabled>
                    <x-heroicon-o-arrow-up-tray class="btn-icon-svg" />
                    Import Schema
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
// ── Export dropdown ──────────────────────────────────────────────────────────
(function () {
    const btn  = document.getElementById('export-dropdown-btn');
    const menu = document.getElementById('export-dropdown-menu');
    if (!btn || !menu) return;

    function open()  { btn.setAttribute('aria-expanded', 'true');  menu.classList.add('open'); }
    function close() { btn.setAttribute('aria-expanded', 'false'); menu.classList.remove('open'); }
    function toggle() { btn.getAttribute('aria-expanded') === 'true' ? close() : open(); }

    btn.addEventListener('click', (e) => { e.stopPropagation(); toggle(); });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('export-dropdown').contains(e.target)) close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });
})();

// ── Import file picker / drop zone ──────────────────────────────────────────
(function () {
    const dropZone   = document.getElementById('import-drop-zone');
    const fileInput  = document.getElementById('schema');
    const label      = document.getElementById('import-file-label');
    const chosen     = document.getElementById('import-file-chosen');
    const chosenName = document.getElementById('import-chosen-name');
    const clearBtn   = document.getElementById('import-clear-btn');
    const submitBtn  = document.getElementById('import-submit-btn');

    if (!dropZone) return;

    function showFile(file) {
        chosenName.textContent = file.name;
        label.style.display  = 'none';
        chosen.style.display = 'flex';
        submitBtn.disabled   = false;
    }

    function clearFile() {
        fileInput.value      = '';
        label.style.display  = '';
        chosen.style.display = 'none';
        submitBtn.disabled   = true;
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) showFile(fileInput.files[0]);
        else clearFile();
    });

    clearBtn.addEventListener('click', clearFile);

    // Click on the drop zone → open file picker
    dropZone.addEventListener('click', (e) => {
        if (e.target === clearBtn || clearBtn.contains(e.target)) return;
        if (chosen.style.display !== 'none') return;
        fileInput.click();
    });

    // Drag-and-drop
    dropZone.addEventListener('dragover',  (e) => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', ()  => { dropZone.classList.remove('drag-over'); });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showFile(file);
        }
    });
})();
</script>
@endsection