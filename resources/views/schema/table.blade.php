@extends('layouts.app')

@section('title', 'Table: ' . $table->name . ' — SchemaBuilder')

@section('head')
<style>
/* ===========================
   DESIGN TOKENS
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
    --shadow-glow: 0 0 0 1px var(--accent), 0 4px 24px rgba(79,110,247,.2);
    --font-mono:   'JetBrains Mono', 'Fira Code', monospace;
}

/* ===========================
   RESET / BASE
=========================== */
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
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 20px 80px;
}

/* ===========================
   BREADCRUMB
=========================== */
.breadcrumb {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.breadcrumb a {
    color: var(--accent);
    text-decoration: none;
    transition: color var(--transition);
}
.breadcrumb a:hover { color: #a5b4fc; }
.breadcrumb .sep { color: var(--border); }

/* ===========================
   PAGE HEADER
=========================== */
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.page-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}
.page-header h1 .icon {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    border-radius: var(--radius-sm);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}

/* ===========================
   STATUS BAR
=========================== */
.status-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    padding: 8px 14px;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: var(--bg-card);
    transition: all 0.3s ease;
    min-width: 200px;
}
.status-bar .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--text-muted);
    transition: background 0.3s ease;
    flex-shrink: 0;
}
.status-bar.saved .dot    { background: var(--success); }
.status-bar.pending .dot  { background: var(--warning); animation: pulse 1.2s infinite; }
.status-bar.error .dot    { background: var(--danger); }
.status-bar .status-text  { color: var(--text-dim); }
.status-bar.saved .status-text  { color: var(--success); }
.status-bar.pending .status-text { color: var(--warning); }
.status-bar.error .status-text  { color: var(--danger); }

@keyframes pulse {
    0%,100% { opacity: 1; }
    50%      { opacity: 0.4; }
}

/* ===========================
   SAVE BUTTON
=========================== */
.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    padding: 9px 18px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity var(--transition), transform var(--transition);
    box-shadow: 0 2px 12px rgba(79,110,247,.4);
}
.btn-save:hover:not(:disabled) { opacity: 0.85; transform: translateY(-1px); }
.btn-save:active { transform: translateY(0); }
.btn-save:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

/* ===========================
   TABLE NAME SECTION
=========================== */
.section-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 20px;
}
.section-card h2 {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-muted);
    margin-bottom: 14px;
}

.field-row {
    display: grid;
    grid-template-columns: 140px 1fr;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.field-row:last-child { margin-bottom: 0; }
.field-row label {
    font-size: 13px;
    color: var(--text-dim);
    font-weight: 500;
}

input[type="text"],
input[type="number"],
select,
textarea {
    width: 100%;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text);
    font-family: inherit;
    font-size: 13px;
    padding: 8px 12px;
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition);
}
input:focus, select:focus, textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(79,110,247,.15);
}
input[type="text"].mono { font-family: var(--font-mono); }
select option { background: var(--bg-elevated); }

/* ===========================
   COLUMNS SECTION
=========================== */
.columns-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 10px;
}
.columns-header h2 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
}
.columns-header .count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px; height: 22px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 50%;
    font-size: 11px;
    color: var(--text-muted);
    margin-left: 8px;
}

.btn-add-col {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text-dim);
    font-family: inherit;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 12px;
    cursor: pointer;
    transition: all var(--transition);
}
.btn-add-col:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: rgba(79,110,247,.06);
}

/* ===========================
   COLUMN ROW
=========================== */
.columns-list { display: flex; flex-direction: column; gap: 10px; }

.column-row {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    transition: border-color var(--transition), box-shadow var(--transition);
    overflow: hidden;
}
.column-row:hover { border-color: #333659; }
.column-row.dragging {
    box-shadow: 0 8px 32px rgba(0,0,0,.6);
    opacity: 0.85;
    border-color: var(--accent);
}

.col-main {
    display: grid;
    grid-template-columns: 28px 1fr 180px auto auto;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
}

.drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--border);
    cursor: grab;
    font-size: 16px;
    transition: color var(--transition);
}
.drag-handle:hover { color: var(--text-muted); }
.drag-handle:active { cursor: grabbing; }

.col-name-wrap {
    position: relative;
}
.col-name-wrap input {
    font-family: var(--font-mono);
    font-size: 13px;
}

.col-badges {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    align-items: center;
}
.badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
    letter-spacing: .3px;
}
.badge-pk { background: rgba(245,158,11,.12); color: #fbbf24; border: 1px solid rgba(245,158,11,.25); }
.badge-fk { background: rgba(124,58,237,.12); color: #a78bfa; border: 1px solid rgba(124,58,237,.25); }
.badge-uq { background: rgba(34,197,94,.12); color: #4ade80; border: 1px solid rgba(34,197,94,.25); }
.badge-nn { background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.25); }
.badge-ai { background: rgba(79,110,247,.12); color: #818cf8; border: 1px solid rgba(79,110,247,.25); }

.col-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px; height: 30px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--radius-sm);
    color: var(--text-muted);
    font-size: 14px;
    cursor: pointer;
    transition: all var(--transition);
    flex-shrink: 0;
}
.btn-icon:hover { background: var(--bg-elevated); border-color: var(--border); color: var(--text); }
.btn-icon.danger:hover { background: rgba(239,68,68,.1); border-color: rgba(239,68,68,.3); color: var(--danger); }
.btn-icon.accent:hover { background: rgba(79,110,247,.1); border-color: rgba(79,110,247,.3); color: var(--accent); }

/* Expanded column panel */
.col-expanded {
    display: none;
    border-top: 1px solid var(--border);
    background: var(--bg-elevated);
    padding: 16px 14px 16px 52px;
    gap: 14px;
    flex-wrap: wrap;
}
.col-expanded.open { display: flex; }

.exp-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 140px;
}
.exp-group label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--text-muted);
}
.exp-group input,
.exp-group select {
    padding: 6px 10px;
    font-size: 12px;
}

/* Toggle switches */
.toggle-row {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 110px;
}
.toggle-row label { font-size: 12px; color: var(--text-dim); cursor: pointer; }

.toggle {
    position: relative;
    width: 34px;
    height: 18px;
    flex-shrink: 0;
}
.toggle input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 9px;
    cursor: pointer;
    transition: background var(--transition), border-color var(--transition);
}
.toggle-slider::before {
    content: '';
    position: absolute;
    width: 12px; height: 12px;
    background: var(--text-muted);
    border-radius: 50%;
    top: 2px; left: 2px;
    transition: transform var(--transition), background var(--transition);
}
.toggle input:checked + .toggle-slider { background: var(--accent); border-color: var(--accent); }
.toggle input:checked + .toggle-slider::before { transform: translateX(16px); background: #fff; }

/* ===========================
   FK DIALOG
=========================== */
dialog {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    color: var(--text);
    padding: 0;
    width: min(480px, 94vw);
    box-shadow: var(--shadow);
    outline: none;
    overflow: hidden;
}
dialog::backdrop {
    background: rgba(0,0,0,.65);
    backdrop-filter: blur(4px);
}
.dialog-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.dialog-header h3 { font-size: 15px; font-weight: 600; }
.dialog-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.dialog-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid var(--border);
    background: var(--bg-elevated);
}
.btn-primary {
    background: linear-gradient(135deg, var(--accent), var(--accent-2));
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    padding: 8px 18px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity var(--transition);
}
.btn-primary:hover { opacity: 0.85; }
.btn-ghost {
    background: transparent;
    color: var(--text-muted);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 8px 14px;
    font-family: inherit;
    font-size: 13px;
    cursor: pointer;
    transition: all var(--transition);
}
.btn-ghost:hover { border-color: var(--text-muted); color: var(--text); }

/* ===========================
   TOAST NOTIFICATIONS
=========================== */
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
}
.toast {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: var(--radius-sm);
    border: 1px solid transparent;
    font-size: 13px;
    font-weight: 500;
    min-width: 260px;
    max-width: 380px;
    box-shadow: var(--shadow);
    animation: slideIn .25s ease;
    pointer-events: all;
    background: var(--bg-card);
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.toast.success { border-color: rgba(34,197,94,.3); color: var(--success); }
.toast.error   { border-color: rgba(239,68,68,.3); color: var(--danger); }
.toast.info    { border-color: rgba(79,110,247,.3); color: #818cf8; }
.toast.fading  { opacity: 0; transform: translateX(20px); }
@keyframes slideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }

/* ===========================
   EMPTY STATE
=========================== */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-muted);
    border: 2px dashed var(--border);
    border-radius: var(--radius);
}
.empty-state p { margin-top: 8px; font-size: 13px; }

/* ===========================
   TYPE TAG on column header
=========================== */
.col-type-tag {
    font-family: var(--font-mono);
    font-size: 11px;
    padding: 3px 8px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 4px;
    color: #a5b4fc;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

/* drag-over visual */
.col-drag-over { border-color: var(--accent) !important; box-shadow: var(--shadow-glow); }

/* Autosave countdown badge */
#autosave-countdown {
    font-size: 11px;
    color: var(--warning);
    font-weight: 600;
    display: none;
}
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
        <span>{{ $table->name }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon">⊞</span>
            <span id="header-table-name">{{ $table->name }}</span>
        </h1>
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div class="status-bar" id="status-bar">
                <span class="dot"></span>
                <span class="status-text" id="status-text">Ready</span>
                <span id="autosave-countdown"></span>
            </div>
            <button class="btn-save" id="btn-manual-save" title="Save (Ctrl+S)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Table
            </button>
        </div>
    </div>

    {{-- Table Name --}}
    <div class="section-card">
        <h2>Table Settings</h2>
        <div class="field-row">
            <label for="input-table-name">Table Name</label>
            <input type="text" id="input-table-name" class="mono" value="{{ $table->name }}" placeholder="e.g. users" autocomplete="off">
        </div>
    </div>

    {{-- Columns --}}
    <div class="section-card" style="padding:20px 20px 24px;">
        <div class="columns-header">
            <h2>Columns <span class="count" id="col-count">{{ $table->columns->count() }}</span></h2>
            <button class="btn-add-col" id="btn-add-column">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Column
            </button>
        </div>

        <div class="columns-list" id="columns-list">
            @forelse($table->columns as $col)
            <div class="column-row"
                 data-id="{{ $col->id }}"
                 data-name="{{ $col->name }}"
                 draggable="true">
                <div class="col-main">
                    <div class="drag-handle" title="Drag to reorder">⠿</div>
                    <div class="col-name-wrap">
                        <input type="text" class="col-input-name mono"
                               value="{{ $col->name }}" placeholder="column_name" autocomplete="off"
                               aria-label="Column name">
                    </div>
                    <select class="col-input-type" aria-label="Column type">
                        @php
                        $types = ['bigint','bigIncrements','binary','boolean','char','date','dateTime','decimal','double','enum','float','foreignId','id','integer','json','jsonb','longText','mediumInteger','mediumText','nullableTimestamps','smallInteger','softDeletes','string','text','time','timestamp','timestamps','tinyInteger','tinyText','unsignedBigInteger','unsignedInteger','uuid','ulid','year'];
                        @endphp
                        @foreach($types as $t)
                            <option value="{{ $t }}" @selected($col->type === $t)>{{ $t }}</option>
                        @endforeach
                    </select>
                    <div class="col-badges" id="badges-{{ $col->id }}">
                        @if($col->is_primary)  <span class="badge badge-pk">PK</span>  @endif
                        @if($col->is_unique)   <span class="badge badge-uq">UQ</span>  @endif
                        @if(!$col->is_nullable)<span class="badge badge-nn">NN</span>  @endif
                        @if($col->auto_increment)<span class="badge badge-ai">AI</span>@endif
                        @if($col->referenced_table_id)<span class="badge badge-fk">FK</span>@endif
                    </div>
                    <div class="col-actions">
                        <button class="btn-icon accent btn-config-fk" title="Configure Foreign Key"
                            data-col-id="{{ $col->id }}"
                            data-referenced="{{ $col->referenced_table_id ?? '' }}"
                            data-cascade="{{ $col->on_cascade ?? '' }}">🔗</button>
                        <button class="btn-icon btn-toggle-expand" title="Expand / collapse modifiers">⚙</button>
                        <button class="btn-icon danger btn-delete-col" title="Delete column">✕</button>
                    </div>
                </div>
                <div class="col-expanded" id="exp-{{ $col->id }}">
                    <div class="exp-group">
                        <label>Default Value</label>
                        <input type="text" class="col-input-default" value="{{ $col->default ?? '' }}" placeholder="NULL">
                    </div>
                    <div class="exp-group">
                        <label>Length / Precision</label>
                        <input type="number" class="col-input-length" min="1" value="{{ $col->length ?? '' }}" placeholder="e.g. 255">
                    </div>
                    <div class="toggle-row">
                        <label class="toggle">
                            <input type="checkbox" class="col-input-nullable" @checked($col->is_nullable)>
                            <span class="toggle-slider"></span>
                        </label>
                        <label>Nullable</label>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle">
                            <input type="checkbox" class="col-input-primary" @checked($col->is_primary)>
                            <span class="toggle-slider"></span>
                        </label>
                        <label>Primary Key</label>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle">
                            <input type="checkbox" class="col-input-unique" @checked($col->is_unique)>
                            <span class="toggle-slider"></span>
                        </label>
                        <label>Unique</label>
                    </div>
                    <div class="toggle-row">
                        <label class="toggle">
                            <input type="checkbox" class="col-input-autoincrement" @checked($col->auto_increment)>
                            <span class="toggle-slider"></span>
                        </label>
                        <label>Auto Increment</label>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state" id="empty-state">
                <div style="font-size:28px;">⊡</div>
                <p>No columns yet. Click <strong>Add Column</strong> to get started.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- FK Dialog --}}
<dialog id="fk-dialog" aria-labelledby="fk-dialog-title">
    <div class="dialog-header">
        <h3 id="fk-dialog-title">🔗 Configure Foreign Key</h3>
        <button class="btn-icon" id="fk-dialog-close">✕</button>
    </div>
    <div class="dialog-body">
        <div class="exp-group">
            <label for="fk-select-table">Referenced Table</label>
            <select id="fk-select-table">
                <option value="">— None (remove FK) —</option>
                @foreach($allTables as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="exp-group">
            <label for="fk-cascade">On Delete Cascade</label>
            <select id="fk-cascade">
                <option value="">No action</option>
                <option value="CASCADE">CASCADE</option>
                <option value="SET NULL">SET NULL</option>
                <option value="RESTRICT">RESTRICT</option>
                <option value="NO ACTION">NO ACTION</option>
            </select>
        </div>
        <div id="fk-preview" style="font-size:11px;color:var(--text-muted);font-family:var(--font-mono);"></div>
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
//  DATA: seed initial state from server-rendered Blade data
// ============================================================
const CSRF      = document.querySelector('meta[name="csrf-token"]').content;
const SAVE_URL  = @json(route('schema.table.update', [$project->slug, $database->name, $table->name]));
const ALL_TABLES = @json($allTables->map(fn($t) => ['id' => $t->id, 'name' => $t->name]));

const COLUMN_TYPES = [
    'bigint','bigIncrements','binary','boolean','char','date','dateTime',
    'decimal','double','enum','float','foreignId','id','integer','json',
    'jsonb','longText','mediumInteger','mediumText','nullableTimestamps',
    'smallInteger','softDeletes','string','text','time','timestamp',
    'timestamps','tinyInteger','tinyText','unsignedBigInteger','unsignedInteger',
    'uuid','ulid','year'
];

// Seed columns from server
let columns = @json($table->columns->map(fn($c) => [
    'id'                  => $c->id,
    'name'                => $c->name,
    'type'                => $c->type,
    'is_nullable'         => (bool)$c->is_nullable,
    'is_primary'          => (bool)$c->is_primary,
    'is_unique'           => (bool)$c->is_unique,
    'auto_increment'      => (bool)$c->auto_increment,
    'default'             => $c->default,
    'length'              => $c->length,
    'on_cascade'          => $c->on_cascade,
    'referenced_table_id' => $c->referenced_table_id,
]));

let tableName = @json($table->name);
let lastSavedJson = JSON.stringify(buildPayload());

// ============================================================
//  DOM REFS
// ============================================================
const colList         = document.getElementById('columns-list');
const colCount        = document.getElementById('col-count');
const statusBar       = document.getElementById('status-bar');
const statusText      = document.getElementById('status-text');
const headerName      = document.getElementById('header-table-name');
const inputTableName  = document.getElementById('input-table-name');
const btnManualSave   = document.getElementById('btn-manual-save');
const btnAddCol       = document.getElementById('btn-add-column');
const fkDialog        = document.getElementById('fk-dialog');
const fkSelectTable   = document.getElementById('fk-select-table');
const fkCascade       = document.getElementById('fk-cascade');
const fkPreview       = document.getElementById('fk-preview');
const toastContainer  = document.getElementById('toast-container');
const autosaveDisplay = document.getElementById('autosave-countdown');

// ============================================================
//  STATE HELPERS
// ============================================================
function buildPayload() {
    return {
        name: tableName,
        columns: columns,
    };
}

function isDirty() {
    return JSON.stringify(buildPayload()) !== lastSavedJson;
}

// ============================================================
//  STATUS BAR
// ============================================================
function setStatus(state, msg) {
    statusBar.className = 'status-bar ' + state;
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
    toastContainer.appendChild(el);
    setTimeout(() => {
        el.classList.add('fading');
        setTimeout(() => el.remove(), 350);
    }, 3500);
}

// ============================================================
//  SAVE (FETCH)
// ============================================================
let saveInProgress = false;
async function saveSchema() {
    if (saveInProgress) return;
    const payload = buildPayload();
    if (!payload.name.trim()) { toast('Table name is required.', 'error'); return; }

    saveInProgress = true;
    btnManualSave.disabled = true;
    setStatus('pending', 'Saving…');

    try {
        const res = await fetch(SAVE_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (res.ok && data.success) {
            // Update local column IDs from server response
            if (data.columns) {
                data.columns.forEach((serverCol, i) => {
                    if (columns[i]) columns[i].id = serverCol.id;
                    // Update the DOM row's data-id
                    const rows = colList.querySelectorAll('.column-row');
                    if (rows[i]) rows[i].dataset.id = serverCol.id;
                });
            }
            lastSavedJson = JSON.stringify(buildPayload());
            setStatus('saved', 'Saved');
            toast('Table saved successfully!', 'success');
            clearAutosaveTimer();
        } else {
            // Validation errors
            let errMsg = 'Validation error.';
            if (data.errors) {
                errMsg = Object.values(data.errors).flat().join(' ');
            } else if (data.message) {
                errMsg = data.message;
            }
            setStatus('error', 'Save failed');
            toast(errMsg, 'error');
        }
    } catch (e) {
        setStatus('error', 'Network error');
        toast('Failed to reach server. Check your connection.', 'error');
    } finally {
        saveInProgress = false;
        btnManualSave.disabled = false;
    }
}

// ============================================================
//  AUTO-SAVE (debounced, 10 000 ms)
// ============================================================
const AUTOSAVE_DELAY = 10000;
let autosaveTimer    = null;
let countdownInterval = null;
let countdownEnd      = null;

function clearAutosaveTimer() {
    clearTimeout(autosaveTimer);
    clearInterval(countdownInterval);
    autosaveTimer = null;
    countdownEnd  = null;
    autosaveDisplay.style.display = 'none';
}

function scheduleAutosave() {
    clearAutosaveTimer();
    countdownEnd = Date.now() + AUTOSAVE_DELAY;
    autosaveDisplay.style.display = '';

    countdownInterval = setInterval(() => {
        const remaining = Math.max(0, Math.ceil((countdownEnd - Date.now()) / 1000));
        autosaveDisplay.textContent = `Auto-save in ${remaining}s`;
        if (remaining === 0) clearInterval(countdownInterval);
    }, 500);

    autosaveTimer = setTimeout(async () => {
        clearAutosaveTimer();
        if (isDirty()) await saveSchema();
    }, AUTOSAVE_DELAY);
}

// ============================================================
//  CHANGE DETECTION → trigger autosave & status
// ============================================================
function onStateChange() {
    if (isDirty()) {
        setStatus('pending', 'Unsaved changes');
        scheduleAutosave();
    } else {
        setStatus('saved', 'Saved');
        clearAutosaveTimer();
    }
    colCount.textContent = columns.length;
    headerName.textContent = tableName;
}

// ============================================================
//  TABLE NAME INPUT
// ============================================================
inputTableName.addEventListener('input', () => {
    tableName = inputTableName.value.trim();
    onStateChange();
});

// ============================================================
//  COLUMN: read from DOM row into state
// ============================================================
function readColFromRow(row, colObj) {
    colObj.name            = row.querySelector('.col-input-name').value.trim();
    colObj.type            = row.querySelector('.col-input-type').value;
    colObj.is_nullable     = row.querySelector('.col-input-nullable').checked;
    colObj.is_primary      = row.querySelector('.col-input-primary').checked;
    colObj.is_unique       = row.querySelector('.col-input-unique').checked;
    colObj.auto_increment  = row.querySelector('.col-input-autoincrement').checked;
    const defVal           = row.querySelector('.col-input-default').value.trim();
    colObj.default         = defVal || null;
    const lenVal           = row.querySelector('.col-input-length').value;
    colObj.length          = lenVal ? parseInt(lenVal, 10) : null;
}

// ============================================================
//  COLUMN: update badges
// ============================================================
function updateBadges(row, col) {
    const expId = row.dataset.id || row.dataset.tempid;
    const badgeContainer = row.querySelector('.col-badges');
    if (!badgeContainer) return;
    badgeContainer.innerHTML = '';
    if (col.is_primary)          badgeContainer.innerHTML += '<span class="badge badge-pk">PK</span>';
    if (col.is_unique)           badgeContainer.innerHTML += '<span class="badge badge-uq">UQ</span>';
    if (!col.is_nullable)        badgeContainer.innerHTML += '<span class="badge badge-nn">NN</span>';
    if (col.auto_increment)      badgeContainer.innerHTML += '<span class="badge badge-ai">AI</span>';
    if (col.referenced_table_id) badgeContainer.innerHTML += '<span class="badge badge-fk">FK</span>';
}

// ============================================================
//  CREATE COLUMN ROW DOM
// ============================================================
let tempIdCounter = 0;
function buildColumnRow(col) {
    const tempId = col.id || ('_new_' + (++tempIdCounter));
    const row = document.createElement('div');
    row.className   = 'column-row';
    row.dataset.id  = col.id || '';
    row.dataset.tempid = tempId;
    row.draggable   = true;

    const typeOptions = COLUMN_TYPES.map(t =>
        `<option value="${t}"${t === col.type ? ' selected' : ''}>${t}</option>`
    ).join('');

    row.innerHTML = `
        <div class="col-main">
            <div class="drag-handle" title="Drag to reorder">⠿</div>
            <div class="col-name-wrap">
                <input type="text" class="col-input-name mono"
                       value="${escHtml(col.name)}" placeholder="column_name" autocomplete="off"
                       aria-label="Column name">
            </div>
            <select class="col-input-type" aria-label="Column type">
                ${typeOptions}
            </select>
            <div class="col-badges"></div>
            <div class="col-actions">
                <button class="btn-icon accent btn-config-fk" title="Configure Foreign Key"
                    data-col-id="${escHtml(col.id||tempId)}"
                    data-referenced="${col.referenced_table_id||''}"
                    data-cascade="${col.on_cascade||''}">🔗</button>
                <button class="btn-icon btn-toggle-expand" title="Expand / collapse modifiers">⚙</button>
                <button class="btn-icon danger btn-delete-col" title="Delete column">✕</button>
            </div>
        </div>
        <div class="col-expanded" id="exp-${escHtml(tempId)}">
            <div class="exp-group">
                <label>Default Value</label>
                <input type="text" class="col-input-default" value="${escHtml(col.default||'')}" placeholder="NULL">
            </div>
            <div class="exp-group">
                <label>Length / Precision</label>
                <input type="number" class="col-input-length" min="1" value="${col.length||''}" placeholder="e.g. 255">
            </div>
            <div class="toggle-row">
                <label class="toggle">
                    <input type="checkbox" class="col-input-nullable"${col.is_nullable?' checked':''}>
                    <span class="toggle-slider"></span>
                </label>
                <label>Nullable</label>
            </div>
            <div class="toggle-row">
                <label class="toggle">
                    <input type="checkbox" class="col-input-primary"${col.is_primary?' checked':''}>
                    <span class="toggle-slider"></span>
                </label>
                <label>Primary Key</label>
            </div>
            <div class="toggle-row">
                <label class="toggle">
                    <input type="checkbox" class="col-input-unique"${col.is_unique?' checked':''}>
                    <span class="toggle-slider"></span>
                </label>
                <label>Unique</label>
            </div>
            <div class="toggle-row">
                <label class="toggle">
                    <input type="checkbox" class="col-input-autoincrement"${col.auto_increment?' checked':''}>
                    <span class="toggle-slider"></span>
                </label>
                <label>Auto Increment</label>
            </div>
        </div>
    `;

    attachRowListeners(row, col);
    updateBadges(row, col);
    return row;
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ============================================================
//  COLUMN ROW EVENT LISTENERS
// ============================================================
function attachRowListeners(row, colObj) {
    // All inputs trigger change detection
    row.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', () => {
            readColFromRow(row, colObj);
            updateBadges(row, colObj);
            onStateChange();
        });
        input.addEventListener('change', () => {
            readColFromRow(row, colObj);
            updateBadges(row, colObj);
            onStateChange();
        });
    });

    // Expand toggle
    row.querySelector('.btn-toggle-expand').addEventListener('click', () => {
        const panel = row.querySelector('.col-expanded');
        panel.classList.toggle('open');
    });

    // Delete column
    row.querySelector('.btn-delete-col').addEventListener('click', () => {
        const idx = columns.indexOf(colObj);
        if (idx !== -1) columns.splice(idx, 1);
        row.style.opacity = '0';
        row.style.transform = 'translateX(-10px)';
        row.style.transition = 'all 0.2s ease';
        setTimeout(() => {
            row.remove();
            checkEmptyState();
            onStateChange();
        }, 200);
    });

    // FK configure
    row.querySelector('.btn-config-fk').addEventListener('click', () => {
        openFkDialog(colObj, row);
    });

    // Drag & Drop
    row.addEventListener('dragstart', (e) => {
        e.dataTransfer.effectAllowed = 'move';
        row.classList.add('dragging');
        dragSrc = row;
    });
    row.addEventListener('dragend', () => {
        row.classList.remove('dragging');
        document.querySelectorAll('.col-drag-over').forEach(r => r.classList.remove('col-drag-over'));
        syncColumnsOrderFromDOM();
        onStateChange();
    });
    row.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        if (dragSrc && dragSrc !== row) {
            document.querySelectorAll('.col-drag-over').forEach(r => r.classList.remove('col-drag-over'));
            row.classList.add('col-drag-over');
        }
    });
    row.addEventListener('drop', (e) => {
        e.preventDefault();
        row.classList.remove('col-drag-over');
        if (dragSrc && dragSrc !== row) {
            const parent = colList;
            const rows = [...parent.querySelectorAll('.column-row')];
            const srcIdx = rows.indexOf(dragSrc);
            const dstIdx = rows.indexOf(row);
            if (srcIdx < dstIdx) {
                parent.insertBefore(dragSrc, row.nextSibling);
            } else {
                parent.insertBefore(dragSrc, row);
            }
        }
    });
}

let dragSrc = null;

function syncColumnsOrderFromDOM() {
    const rows = colList.querySelectorAll('.column-row');
    const reordered = [];
    rows.forEach(row => {
        const tempId = row.dataset.tempid;
        const col = columns.find(c => {
            const tid = c.id || ('_new_' + /* can't find without ref */ tempId);
            return row.dataset.id ? (c.id === row.dataset.id) : (c._tempid === tempId);
        });
        if (col) reordered.push(col);
    });
    if (reordered.length === columns.length) columns.splice(0, columns.length, ...reordered);
}

// ============================================================
//  EMPTY STATE CHECK
// ============================================================
function checkEmptyState() {
    const rows = colList.querySelectorAll('.column-row');
    let emptyEl = document.getElementById('empty-state');
    if (rows.length === 0) {
        if (!emptyEl) {
            emptyEl = document.createElement('div');
            emptyEl.id = 'empty-state';
            emptyEl.className = 'empty-state';
            emptyEl.innerHTML = '<div style="font-size:28px;">⊡</div><p>No columns yet. Click <strong>Add Column</strong> to get started.</p>';
            colList.appendChild(emptyEl);
        }
    } else {
        if (emptyEl) emptyEl.remove();
    }
}

// ============================================================
//  ADD COLUMN
// ============================================================
btnAddCol.addEventListener('click', () => {
    const newCol = {
        id: null,
        name: '',
        type: 'string',
        is_nullable: false,
        is_primary: false,
        is_unique: false,
        auto_increment: false,
        default: null,
        length: null,
        on_cascade: null,
        referenced_table_id: null,
    };
    columns.push(newCol);

    checkEmptyState(); // remove empty state if present
    const row = buildColumnRow(newCol);
    colList.appendChild(row);
    // Expand immediately & focus name
    row.querySelector('.col-expanded').classList.add('open');
    row.querySelector('.col-input-name').focus();
    onStateChange();
});

// ============================================================
//  FK DIALOG
// ============================================================
let fkTargetCol  = null;
let fkTargetRow  = null;

function openFkDialog(col, row) {
    fkTargetCol = col;
    fkTargetRow = row;
    fkSelectTable.value = col.referenced_table_id || '';
    fkCascade.value     = col.on_cascade || '';
    updateFkPreview();
    fkDialog.showModal();
}

function updateFkPreview() {
    const tableId  = fkSelectTable.value;
    const cascade  = fkCascade.value;
    const tableName = ALL_TABLES.find(t => t.id === tableId);
    if (tableId && tableName) {
        fkPreview.textContent = `FOREIGN KEY → ${tableName.name}${cascade ? ' ON DELETE ' + cascade : ''}`;
    } else {
        fkPreview.textContent = 'No foreign key will be applied.';
    }
}

fkSelectTable.addEventListener('change', updateFkPreview);
fkCascade.addEventListener('change', updateFkPreview);

document.getElementById('fk-dialog-close').addEventListener('click', () => fkDialog.close());
document.getElementById('fk-dialog-cancel').addEventListener('click', () => fkDialog.close());
document.getElementById('fk-dialog-apply').addEventListener('click', () => {
    if (fkTargetCol) {
        fkTargetCol.referenced_table_id = fkSelectTable.value || null;
        fkTargetCol.on_cascade          = fkCascade.value || null;
        if (fkTargetRow) {
            updateBadges(fkTargetRow, fkTargetCol);
            // Update btn data attrs
            const btn = fkTargetRow.querySelector('.btn-config-fk');
            if (btn) {
                btn.dataset.referenced = fkTargetCol.referenced_table_id || '';
                btn.dataset.cascade    = fkTargetCol.on_cascade || '';
            }
        }
        onStateChange();
    }
    fkDialog.close();
});

// Close on backdrop click
fkDialog.addEventListener('click', (e) => {
    if (e.target === fkDialog) fkDialog.close();
});

// ============================================================
//  MANUAL SAVE BUTTON
// ============================================================
btnManualSave.addEventListener('click', saveSchema);

// ============================================================
//  KEYBOARD SHORTCUT: Ctrl/Cmd + S
// ============================================================
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveSchema();
    }
});

// ============================================================
//  INIT: attach listeners to server-rendered rows
// ============================================================
(function initServerRows() {
    const rows = colList.querySelectorAll('.column-row');
    rows.forEach((row, i) => {
        const col = columns[i];
        if (!col) return;
        row.dataset.tempid = col.id || ('_new_' + (++tempIdCounter));
        attachRowListeners(row, col);
    });
    checkEmptyState();
    setStatus('saved', 'Saved');
})();
</script>
@endsection
