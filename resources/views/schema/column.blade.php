@extends('layouts.app')

@section('title', 'Column: ' . $column->name . ' — SchemaBuilder')

@section('head')
<style>
/* ===========================
   DESIGN TOKENS (shared)
=========================== */
:root {
    --bg:          #0d0f17;
    --bg-card:     #13161f;
    --bg-elevated: #1a1d2e;
    --bg-input:    #0f1120;
    --border:      #252840;
    --border-glow: #3b4bdb;
    --accent:      #4f6ef7;
    --accent-2:    #7c3aed;
    --danger:      #ef4444;
    --success:     #22c55e;
    --warning:     #f59e0b;
    --text:        #e2e8f0;
    --text-muted:  #64748b;
    --text-dim:    #94a3b8;
    --radius:      10px;
    --radius-sm:   6px;
    --transition:  150ms ease;
    --shadow:      0 4px 24px rgba(0,0,0,.5);
    --font-mono:   'JetBrains Mono', 'Fira Code', monospace;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 14px;
    line-height: 1.5;
    min-height: 100vh;
}

/* ===========================
   PAGE SHELL
=========================== */
.page {
    max-width: 820px;
    margin: 0 auto;
    padding: 24px 20px 80px;
}

/* ===========================
   BREADCRUMB
=========================== */
.breadcrumb {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; color: var(--text-muted);
    margin-bottom: 28px; flex-wrap: wrap;
}
.breadcrumb a { color: var(--accent); text-decoration: none; transition: color var(--transition); }
.breadcrumb a:hover { color: #a5b4fc; }
.breadcrumb .sep { color: var(--border); }

/* ===========================
   PAGE HEADER
=========================== */
.page-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 16px;
    margin-bottom: 28px; flex-wrap: wrap;
}
.page-header h1 {
    font-size: 22px; font-weight: 700; color: #fff;
    display: flex; align-items: center; gap: 10px;
}
.icon-col {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, #7c3aed, #4f6ef7);
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 15px;
}

/* ===========================
   STATUS BAR
=========================== */
.status-bar {
    display: flex; align-items: center; gap: 10px;
    font-size: 12px; padding: 8px 14px;
    border-radius: var(--radius-sm); border: 1px solid var(--border);
    background: var(--bg-card); transition: all 0.3s ease; min-width: 200px;
}
.status-bar .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--text-muted); transition: background 0.3s ease; flex-shrink: 0;
}
.status-bar.saved .dot   { background: var(--success); }
.status-bar.pending .dot { background: var(--warning); animation: pulse 1.2s infinite; }
.status-bar.error .dot   { background: var(--danger); }
.status-text             { color: var(--text-dim); }
.status-bar.saved .status-text   { color: var(--success); }
.status-bar.pending .status-text { color: var(--warning); }
.status-bar.error .status-text   { color: var(--danger); }
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:0.4} }

/* ===========================
   BUTTONS
=========================== */
.btn-save {
    display: inline-flex; align-items: center; gap: 8px;
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    color: #fff; border: none; border-radius: var(--radius-sm);
    padding: 9px 18px; font-family: inherit; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: opacity var(--transition), transform var(--transition);
    box-shadow: 0 2px 12px rgba(79,110,247,.4);
}
.btn-save:hover:not(:disabled) { opacity: 0.85; transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

.btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    background: transparent; color: var(--text-muted);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    padding: 8px 14px; font-family: inherit; font-size: 13px;
    cursor: pointer; transition: all var(--transition);
}
.btn-secondary:hover { border-color: var(--accent); color: var(--accent); background: rgba(79,110,247,.06); }

.btn-primary {
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    color: #fff; border: none; border-radius: var(--radius-sm);
    padding: 8px 18px; font-family: inherit; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: opacity var(--transition);
}
.btn-primary:hover { opacity: 0.85; }
.btn-ghost {
    background: transparent; color: var(--text-muted);
    border: 1px solid var(--border); border-radius: var(--radius-sm);
    padding: 8px 14px; font-family: inherit; font-size: 13px;
    cursor: pointer; transition: all var(--transition);
}
.btn-ghost:hover { border-color: var(--text-muted); color: var(--text); }
.btn-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; background: transparent;
    border: 1px solid transparent; border-radius: var(--radius-sm);
    color: var(--text-muted); font-size: 14px; cursor: pointer;
    transition: all var(--transition);
}
.btn-icon:hover { background: var(--bg-elevated); border-color: var(--border); color: var(--text); }

/* ===========================
   SECTION CARD
=========================== */
.section-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 22px 24px;
    margin-bottom: 20px;
}
.section-title {
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-muted); margin-bottom: 18px;
    padding-bottom: 12px; border-bottom: 1px solid var(--border);
}

/* ===========================
   FORM GRID
=========================== */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-grid.single { grid-template-columns: 1fr; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.form-group.full { grid-column: 1 / -1; }

.form-group label {
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px;
    color: var(--text-muted);
}

input[type="text"],
input[type="number"],
select,
textarea {
    background: var(--bg-input); border: 1px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text);
    font-family: inherit; font-size: 13px; padding: 9px 12px;
    outline: none; width: 100%;
    transition: border-color var(--transition), box-shadow var(--transition);
}
input:focus, select:focus, textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(79,110,247,.15);
}
.mono { font-family: var(--font-mono); }
select option { background: var(--bg-elevated); }

/* ===========================
   TOGGLE GROUP
=========================== */
.toggle-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 12px;
}

.toggle-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 12px 14px;
    cursor: pointer;
    transition: border-color var(--transition);
    user-select: none;
}
.toggle-card:hover { border-color: var(--accent); }
.toggle-card.active { border-color: var(--accent); background: rgba(79,110,247,.06); }

.toggle-card .tc-label {
    display: flex; flex-direction: column; gap: 2px;
}
.toggle-card .tc-title {
    font-size: 12px; font-weight: 600; color: var(--text);
}
.toggle-card .tc-desc {
    font-size: 10px; color: var(--text-muted);
}

.toggle {
    position: relative; width: 34px; height: 18px; flex-shrink: 0;
}
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; inset: 0;
    background: var(--bg-input); border: 1px solid var(--border);
    border-radius: 9px; cursor: pointer;
    transition: background var(--transition), border-color var(--transition);
}
.toggle-slider::before {
    content: ''; position: absolute;
    width: 12px; height: 12px; background: var(--text-muted);
    border-radius: 50%; top: 2px; left: 2px;
    transition: transform var(--transition), background var(--transition);
}
.toggle input:checked + .toggle-slider { background: var(--accent); border-color: var(--accent); }
.toggle input:checked + .toggle-slider::before { transform: translateX(16px); background: #fff; }

/* ===========================
   FK CARD
=========================== */
.fk-card {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.fk-card-info { display: flex; flex-direction: column; gap: 3px; }
.fk-card-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; font-weight: 600; }
.fk-card-value { font-size: 14px; color: #a78bfa; font-family: var(--font-mono); }
.fk-card-none  { font-size: 13px; color: var(--text-muted); font-style: italic; }

/* ===========================
   SIDEBAR CONTEXT
=========================== */
.layout-cols {
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 700px) {
    .layout-cols { grid-template-columns: 1fr; }
}
.sidebar-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    position: sticky;
    top: 20px;
}
.sidebar-card .sb-header {
    font-size: 11px; font-weight: 600; text-transform: uppercase;
    letter-spacing: 1px; color: var(--text-muted);
    padding: 12px 16px; border-bottom: 1px solid var(--border);
    background: var(--bg-elevated);
}
.sb-col-list { display: flex; flex-direction: column; }
.sb-col-item {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-bottom: 1px solid var(--border);
    transition: background var(--transition);
}
.sb-col-item:last-child { border-bottom: none; }
.sb-col-item.current { background: rgba(79,110,247,.08); border-left: 2px solid var(--accent); }
.sb-col-item a { text-decoration: none; color: var(--text-dim); font-size: 12px; font-family: var(--font-mono); }
.sb-col-item.current a { color: var(--accent); font-weight: 600; }
.sb-col-item a:hover { color: var(--text); }
.sb-type-tag {
    font-size: 10px; color: var(--text-muted);
    background: var(--bg-elevated); border: 1px solid var(--border);
    border-radius: 3px; padding: 1px 5px; font-family: var(--font-mono);
    flex-shrink: 0;
}

/* ===========================
   DIALOG
=========================== */
dialog {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius); color: var(--text); padding: 0;
    width: min(480px, 94vw); box-shadow: var(--shadow);
    outline: none; overflow: hidden;
}
dialog::backdrop { background: rgba(0,0,0,.65); backdrop-filter: blur(4px); }
.dialog-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid var(--border);
}
.dialog-header h3 { font-size: 15px; font-weight: 600; }
.dialog-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.dialog-footer {
    display: flex; justify-content: flex-end; gap: 10px;
    padding: 14px 20px; border-top: 1px solid var(--border);
    background: var(--bg-elevated);
}
.dialog-group { display: flex; flex-direction: column; gap: 6px; }
.dialog-group label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); }

/* ===========================
   TOAST
=========================== */
#toast-container {
    position: fixed; top: 20px; right: 20px; z-index: 9999;
    display: flex; flex-direction: column; gap: 10px; pointer-events: none;
}
.toast {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 16px; border-radius: var(--radius-sm);
    border: 1px solid transparent; font-size: 13px; font-weight: 500;
    min-width: 260px; max-width: 380px; box-shadow: var(--shadow);
    animation: slideIn .25s ease; pointer-events: all;
    background: var(--bg-card); transition: opacity 0.3s ease, transform 0.3s ease;
}
.toast.success { border-color: rgba(34,197,94,.3); color: var(--success); }
.toast.error   { border-color: rgba(239,68,68,.3); color: var(--danger); }
.toast.info    { border-color: rgba(79,110,247,.3); color: #818cf8; }
.toast.fading  { opacity: 0; transform: translateX(20px); }
@keyframes slideIn { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

/* Autosave countdown */
#autosave-countdown { font-size: 11px; color: var(--warning); font-weight: 600; display: none; }
</style>
@endsection

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep">›</span>
        <a href="{{ route('schema.project', ['project' => $project->slug]) }}">{{ $project->name }}</a>
        <span class="sep">›</span>
        <a href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}">{{ $database->name }}</a>
        <span class="sep">›</span>
        <a href="{{ route('schema.table', ['project' => $project->slug, 'database' => $database->name, 'table' => $table->name]) }}">{{ $table->name }}</a>
        <span class="sep">›</span>
        <span>{{ $column->name }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon-col">⊟</span>
            <span id="header-col-name">{{ $column->name }}</span>
        </h1>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div class="status-bar" id="status-bar">
                <span class="dot"></span>
                <span class="status-text" id="status-text">Ready</span>
                <span id="autosave-countdown"></span>
            </div>
            <a class="btn-secondary"
               href="{{ route('schema.table', ['project' => $project->slug, 'database' => $database->name, 'table' => $table->name]) }}">
                ← Back to Table
            </a>
            <button class="btn-save" id="btn-manual-save" title="Save (Ctrl+S)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Column
            </button>
        </div>
    </div>

    <div class="layout-cols">
        <div>
            {{-- Identity --}}
            <div class="section-card">
                <div class="section-title">Identity</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="col-name">Column Name</label>
                        <input type="text" id="col-name" class="mono" value="{{ $column->name }}" placeholder="column_name" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="col-type">Data Type</label>
                        <select id="col-type">
                            @php
                            $types = ['bigint','bigIncrements','binary','boolean','char','date','dateTime','decimal','double','enum','float','foreignId','id','integer','json','jsonb','longText','mediumInteger','mediumText','nullableTimestamps','smallInteger','softDeletes','string','text','time','timestamp','timestamps','tinyInteger','tinyText','unsignedBigInteger','unsignedInteger','uuid','ulid','year'];
                            @endphp
                            @foreach($types as $t)
                                <option value="{{ $t }}" @selected($column->type === $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="col-default">Default Value</label>
                        <input type="text" id="col-default" value="{{ $column->default ?? '' }}" placeholder="NULL">
                    </div>
                    <div class="form-group">
                        <label for="col-length">Length / Precision</label>
                        <input type="number" id="col-length" min="1" value="{{ $column->length ?? '' }}" placeholder="e.g. 255">
                    </div>
                </div>
            </div>

            {{-- Modifiers --}}
            <div class="section-card">
                <div class="section-title">Modifiers</div>
                <div class="toggle-group">
                    <label class="toggle-card {{ $column->is_nullable ? 'active' : '' }}" for="toggle-nullable">
                        <div class="tc-label">
                            <span class="tc-title">Nullable</span>
                            <span class="tc-desc">Allows NULL values</span>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-nullable" @checked($column->is_nullable)>
                            <span class="toggle-slider"></span>
                        </label>
                    </label>
                    <label class="toggle-card {{ $column->is_primary ? 'active' : '' }}" for="toggle-primary">
                        <div class="tc-label">
                            <span class="tc-title">Primary Key</span>
                            <span class="tc-desc">Unique identifier</span>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-primary" @checked($column->is_primary)>
                            <span class="toggle-slider"></span>
                        </label>
                    </label>
                    <label class="toggle-card {{ $column->is_unique ? 'active' : '' }}" for="toggle-unique">
                        <div class="tc-label">
                            <span class="tc-title">Unique</span>
                            <span class="tc-desc">No duplicate values</span>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-unique" @checked($column->is_unique)>
                            <span class="toggle-slider"></span>
                        </label>
                    </label>
                    <label class="toggle-card {{ $column->auto_increment ? 'active' : '' }}" for="toggle-ai">
                        <div class="tc-label">
                            <span class="tc-title">Auto Increment</span>
                            <span class="tc-desc">DB-managed counter</span>
                        </div>
                        <label class="toggle">
                            <input type="checkbox" id="toggle-ai" @checked($column->auto_increment)>
                            <span class="toggle-slider"></span>
                        </label>
                    </label>
                </div>
            </div>

            {{-- Foreign Key --}}
            <div class="section-card">
                <div class="section-title" style="display:flex;align-items:center;justify-content:space-between;">
                    <span>Foreign Key</span>
                    <button class="btn-secondary" id="btn-config-fk" style="padding:4px 10px;font-size:11px;">Configure ›</button>
                </div>
                <div class="fk-card" id="fk-card">
                    @if($column->referenced_table_id)
                        @php $refTable = $allTables->firstWhere('id', $column->referenced_table_id); @endphp
                        <div class="fk-card-info">
                            <span class="fk-card-label">References</span>
                            <span class="fk-card-value" id="fk-display-table">{{ $refTable?->name ?? 'Unknown Table' }}</span>
                        </div>
                        @if($column->on_cascade)
                        <div class="fk-card-info">
                            <span class="fk-card-label">On Delete</span>
                            <span class="fk-card-value" id="fk-display-cascade">{{ $column->on_cascade }}</span>
                        </div>
                        @endif
                    @else
                        <span class="fk-card-none" id="fk-display-none">No foreign key configured.</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: sibling columns --}}
        <div>
            <div class="sidebar-card">
                <div class="sb-header">Columns in {{ $table->name }}</div>
                <div class="sb-col-list">
                    @foreach($table->columns as $sibCol)
                    <div class="sb-col-item {{ $sibCol->id === $column->id ? 'current' : '' }}">
                        @if($sibCol->id === $column->id)
                            <a href="#">{{ $sibCol->name }}</a>
                        @else
                            <a href="{{ route('schema.column', ['project' => $project->slug, 'database' => $database->name, 'table' => $table->name, 'column' => $sibCol->name]) }}">{{ $sibCol->name }}</a>
                        @endif
                        <span class="sb-type-tag">{{ $sibCol->type }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

{{-- FK Dialog --}}
<dialog id="fk-dialog" aria-labelledby="fk-dialog-title">
    <div class="dialog-header">
        <h3 id="fk-dialog-title">🔗 Foreign Key Configuration</h3>
        <button class="btn-icon" id="fk-dialog-close">✕</button>
    </div>
    <div class="dialog-body">
        <div class="dialog-group">
            <label for="fk-select-table">Referenced Table</label>
            <select id="fk-select-table">
                <option value="">— None (remove FK) —</option>
                @foreach($allTables as $t)
                    <option value="{{ $t->id }}" {{ $column->referenced_table_id === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="dialog-group">
            <label for="fk-cascade">On Delete Cascade</label>
            <select id="fk-cascade">
                <option value="" {{ !$column->on_cascade ? 'selected' : '' }}>No action</option>
                <option value="CASCADE"   {{ $column->on_cascade === 'CASCADE'   ? 'selected' : '' }}>CASCADE</option>
                <option value="SET NULL"  {{ $column->on_cascade === 'SET NULL'  ? 'selected' : '' }}>SET NULL</option>
                <option value="RESTRICT"  {{ $column->on_cascade === 'RESTRICT'  ? 'selected' : '' }}>RESTRICT</option>
                <option value="NO ACTION" {{ $column->on_cascade === 'NO ACTION' ? 'selected' : '' }}>NO ACTION</option>
            </select>
        </div>
        <div id="fk-preview" style="font-size:11px;color:var(--text-muted);font-family:var(--font-mono);padding:8px 0;"></div>
    </div>
    <div class="dialog-footer">
        <button class="btn-ghost" id="fk-dialog-cancel">Cancel</button>
        <button class="btn-primary" id="fk-dialog-apply">Apply</button>
    </div>
</dialog>

{{-- Toast container --}}
<div id="toast-container" role="status" aria-live="polite"></div>
@endsection

@section('scripts')
<script>
// ============================================================
//  INITIAL STATE (seeded from Blade)
// ============================================================
const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
const SAVE_URL = "{{ route('schema.column.update', [$project, $database, $table, $column]) }}"

const ALL_TABLES = @json($allTables->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));

let state = {
    name:                @json($column->name),
    type:                @json($column->type),
    is_nullable:         @json((bool)$column->is_nullable),
    is_primary:          @json((bool)$column->is_primary),
    is_unique:           @json((bool)$column->is_unique),
    auto_increment:      @json((bool)$column->auto_increment),
    default:             @json($column->default),
    length:              @json($column->length),
    on_cascade:          @json($column->on_cascade),
    referenced_table_id: @json($column->referenced_table_id),
};

let lastSavedJson = JSON.stringify(state);

// ============================================================
//  DOM REFS
// ============================================================
const colName       = document.getElementById('col-name');
const colType       = document.getElementById('col-type');
const colDefault    = document.getElementById('col-default');
const colLength     = document.getElementById('col-length');
const toggleNull    = document.getElementById('toggle-nullable');
const togglePrim    = document.getElementById('toggle-primary');
const toggleUniq    = document.getElementById('toggle-unique');
const toggleAI      = document.getElementById('toggle-ai');
const statusBar     = document.getElementById('status-bar');
const statusText    = document.getElementById('status-text');
const headerName    = document.getElementById('header-col-name');
const btnSave       = document.getElementById('btn-manual-save');
const fkDialog      = document.getElementById('fk-dialog');
const fkSelectTable = document.getElementById('fk-select-table');
const fkCascade     = document.getElementById('fk-cascade');
const fkPreview     = document.getElementById('fk-preview');
const toastCont     = document.getElementById('toast-container');
const autosaveDisp  = document.getElementById('autosave-countdown');

// Toggle cards
const toggleCards = document.querySelectorAll('.toggle-card');
toggleCards.forEach(card => {
    const checkbox = card.querySelector('input[type="checkbox"]');
    if (checkbox) {
        checkbox.addEventListener('change', () => {
            card.classList.toggle('active', checkbox.checked);
        });
    }
});

// ============================================================
//  READ STATE FROM DOM
// ============================================================
function readState() {
    state.name           = colName.value.trim();
    state.type           = colType.value;
    state.is_nullable    = toggleNull.checked;
    state.is_primary     = togglePrim.checked;
    state.is_unique      = toggleUniq.checked;
    state.auto_increment = toggleAI.checked;
    state.default        = colDefault.value.trim() || null;
    state.length         = colLength.value ? parseInt(colLength.value, 10) : null;
    // FK fields are written directly into state by dialog apply
}

// ============================================================
//  STATUS BAR
// ============================================================
function setStatus(cls, msg) {
    statusBar.className = 'status-bar ' + cls;
    statusText.textContent = msg;
}

// ============================================================
//  TOAST
// ============================================================
function toast(msg, type = 'info') {
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    el.innerHTML = `<span>${icons[type] || '•'}</span><span>${msg}</span>`;
    toastCont.appendChild(el);
    setTimeout(() => {
        el.classList.add('fading');
        setTimeout(() => el.remove(), 350);
    }, 3500);
}

// ============================================================
//  SAVE
// ============================================================
let saveInProgress = false;
async function saveColumn() {
    if (saveInProgress) return;
    if (!state.name.trim()) { toast('Column name is required.', 'error'); return; }

    saveInProgress = true;
    btnSave.disabled = true;
    setStatus('pending', 'Saving…');

    try {
        const res = await fetch(SAVE_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify(state),
        });
        const data = await res.json();
        if (res.ok && data.success) {
            lastSavedJson = JSON.stringify(state);
            headerName.textContent = state.name;
            setStatus('saved', 'Saved');
            toast('Column saved successfully!', 'success');
            clearAutosave();
        } else {
            let errMsg = 'Save failed.';
            if (data.errors) errMsg = Object.values(data.errors).flat().join(' ');
            else if (data.message) errMsg = data.message;
            setStatus('error', 'Save failed');
            toast(errMsg, 'error');
        }
    } catch (e) {
        setStatus('error', 'Network error');
        toast('Failed to reach server.', 'error');
    } finally {
        saveInProgress = false;
        btnSave.disabled = false;
    }
}

// ============================================================
//  AUTO-SAVE
// ============================================================
const AUTOSAVE_MS = 10000;
let autosaveTimer = null;
let countdownIv   = null;

function clearAutosave() {
    clearTimeout(autosaveTimer);
    clearInterval(countdownIv);
    autosaveTimer = null;
    autosaveDisp.style.display = 'none';
}

function scheduleAutosave() {
    clearAutosave();
    const end = Date.now() + AUTOSAVE_MS;
    autosaveDisp.style.display = '';
    countdownIv = setInterval(() => {
        const rem = Math.max(0, Math.ceil((end - Date.now()) / 1000));
        autosaveDisp.textContent = `Auto-save in ${rem}s`;
        if (rem === 0) clearInterval(countdownIv);
    }, 500);
    autosaveTimer = setTimeout(async () => {
        clearAutosave();
        if (JSON.stringify(state) !== lastSavedJson) await saveColumn();
    }, AUTOSAVE_MS);
}

// ============================================================
//  ON CHANGE
// ============================================================
function onChange() {
    readState();
    if (JSON.stringify(state) !== lastSavedJson) {
        setStatus('pending', 'Unsaved changes');
        scheduleAutosave();
    } else {
        setStatus('saved', 'Saved');
        clearAutosave();
    }
}

// Attach to all inputs
[colName, colType, colDefault, colLength, toggleNull, togglePrim, toggleUniq, toggleAI]
    .forEach(el => {
        el.addEventListener('input', onChange);
        el.addEventListener('change', onChange);
    });

// ============================================================
//  FK DIALOG
// ============================================================
function updateFkPreview() {
    const id = fkSelectTable.value;
    const cascade = fkCascade.value;
    const t = ALL_TABLES.find(x => x.id === id);
    fkPreview.textContent = t
        ? `FOREIGN KEY → ${t.name}${cascade ? ' ON DELETE ' + cascade : ''}`
        : 'No foreign key will be applied.';
}

function updateFkCard() {
    const card = document.getElementById('fk-card');
    const id   = state.referenced_table_id;
    const t    = ALL_TABLES.find(x => x.id === id);
    if (id && t) {
        card.innerHTML = `
            <div class="fk-card-info">
                <span class="fk-card-label">References</span>
                <span class="fk-card-value">${t.name}</span>
            </div>
            ${state.on_cascade ? `<div class="fk-card-info">
                <span class="fk-card-label">On Delete</span>
                <span class="fk-card-value">${state.on_cascade}</span>
            </div>` : ''}
        `;
    } else {
        card.innerHTML = '<span class="fk-card-none">No foreign key configured.</span>';
    }
}

document.getElementById('btn-config-fk').addEventListener('click', () => {
    fkSelectTable.value = state.referenced_table_id || '';
    fkCascade.value     = state.on_cascade || '';
    updateFkPreview();
    fkDialog.showModal();
});
fkSelectTable.addEventListener('change', updateFkPreview);
fkCascade.addEventListener('change', updateFkPreview);

document.getElementById('fk-dialog-close').addEventListener('click', () => fkDialog.close());
document.getElementById('fk-dialog-cancel').addEventListener('click', () => fkDialog.close());
document.getElementById('fk-dialog-apply').addEventListener('click', () => {
    state.referenced_table_id = fkSelectTable.value || null;
    state.on_cascade          = fkCascade.value || null;
    updateFkCard();
    onChange();
    fkDialog.close();
});
fkDialog.addEventListener('click', e => { if (e.target === fkDialog) fkDialog.close(); });

// ============================================================
//  MANUAL SAVE & KEYBOARD SHORTCUT
// ============================================================
btnSave.addEventListener('click', saveColumn);
document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveColumn();
    }
});

// Init
setStatus('saved', 'Saved');
</script>
@endsection
