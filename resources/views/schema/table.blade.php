@extends('layouts.app')

@section('title', 'Table: ' . $table->name . ' — SchemaBuilder')

@section('head')
@endsection

@section('content')
<div class="page">

    {{-- Breadcrumb --}}
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('pages.dashboard') }}">Dashboard</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('projects.index') }}">Projects</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('schema.project', ['project' => $project->slug]) }}">{{ $project->name }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <a href="{{ route('schema.database', ['project' => $project->slug, 'database' => $database->name]) }}">{{ $database->name }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ $table->name }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon"><x-heroicon-o-table-cells class="icon-svg" /></span>
            <span id="header-table-name">{{ $table->name }}</span>
        </h1>
        <div class="page-header-actions">
            <div class="status-bar" id="status-bar">
                <span class="dot"></span>
                <span class="status-text" id="status-text">Ready</span>
                <span id="autosave-countdown"></span>
            </div>
            <button class="btn-save" id="btn-manual-save" title="Save (Ctrl+S)">
                <x-heroicon-o-document-arrow-down class="btn-icon-svg" />
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
    <div class="section-card section-card-columns">
        <div class="columns-header">
            <h2>Columns <span class="count" id="col-count">{{ $table->columns->count() }}</span></h2>
            <button class="btn-add-col" id="btn-add-column">
                <x-heroicon-o-plus class="btn-icon-svg" />
                Add Column
            </button>
        </div>

        <div class="columns-list" id="columns-list">
            @forelse($table->columns->sortBy('order_index') as $col)
            <div class="column-row"
                 data-id="{{ $col->id }}"
                 data-name="{{ $col->name }}"
                 draggable="true">
                <div class="col-main">
                    <div class="drag-handle" title="Drag to reorder"><x-heroicon-o-bars-3 class="drag-handle-icon" /></div>
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
                            data-cascade="{{ $col->on_cascade ?? '' }}"><x-heroicon-o-link class="btn-icon-svg" /></button>
                        <button class="btn-icon btn-toggle-expand" title="Expand / collapse modifiers"><x-heroicon-o-cog-6-tooth class="btn-icon-svg" /></button>
                        <button class="btn-icon danger btn-delete-col" title="Delete column"><x-heroicon-o-x-mark class="btn-icon-svg" /></button>
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
                <div class="empty-state-icon"><x-heroicon-o-square-3-stack-3d class="empty-icon-svg" /></div>
                <p>No columns yet. Click <strong>Add Column</strong> to get started.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- FK Dialog --}}
<dialog id="fk-dialog" aria-labelledby="fk-dialog-title">
    <div class="dialog-header">
        <h3 id="fk-dialog-title"><x-heroicon-o-link class="dialog-title-icon" /> Configure Foreign Key</h3>
        <button class="btn-icon" id="fk-dialog-close"><x-heroicon-o-x-mark class="btn-icon-svg" /></button>
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
        <div id="fk-preview" class="fk-preview"></div>
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
const SAVE_URL   = "{{ route('schema.table.update', [$project, $database, $table]) }}";
const ALL_TABLES = {!! json_encode($allTables->map(fn($t) => ['id' => $t->id, 'name' => $t->name])) !!};

const COLUMN_TYPES = [
    'bigint','bigIncrements','binary','boolean','char','date','dateTime',
    'decimal','double','enum','float','foreignId','id','integer','json',
    'jsonb','longText','mediumInteger','mediumText','nullableTimestamps',
    'smallInteger','softDeletes','string','text','time','timestamp',
    'timestamps','tinyInteger','tinyText','unsignedBigInteger','unsignedInteger',
    'uuid','ulid','year'
];

// Seed columns from server
let columns = {{ \Illuminate\Support\Js::from($table->columns->map(fn($c) => [
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
    'order_index'         => $c->order_index,
])) }};

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
    const icons = { success: 'M4.5 12.75l6 6 9-13.5', error: 'M6 18L18 6M6 6l12 12', info: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z' };
    el.innerHTML = `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="${icons[type] || 'M12 12h.01'}"/></svg><span>${msg}</span>`;
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
    if (!col.id) col._tempid = tempId; // Assign _tempid for reordering before save
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
            <div class="drag-handle" title="Drag to reorder"><svg class="drag-handle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg></div>
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
                    data-cascade="${col.on_cascade||''}"><svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg></button>
                <button class="btn-icon btn-toggle-expand" title="Expand / collapse modifiers"><svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/></svg></button>
                <button class="btn-icon danger btn-delete-col" title="Delete column"><svg class="btn-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
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
            emptyEl.innerHTML = '<div class="empty-state-icon"><svg class="empty-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3"/></svg></div><p>No columns yet. Click <strong>Add Column</strong> to get started.</p>';
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
