@extends('layouts.app')

@section('title', 'Column: ' . $column->name . ' — SchemaBuilder')

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
        <a href="{{ route('schema.table', ['project' => $project->slug, 'database' => $database->name, 'table' => $table->name]) }}">{{ $table->name }}</a>
        <span class="sep"><x-heroicon-o-chevron-right class="breadcrumb-sep-icon" /></span>
        <span>{{ $column->name }}</span>
    </nav>

    {{-- Page Header --}}
    <div class="page-header">
        <h1>
            <span class="icon-col"><x-heroicon-o-view-columns class="icon-col-svg" /></span>
            <span id="header-col-name">{{ $column->name }}</span>
        </h1>
        <div class="page-header-actions">
            <div class="status-bar" id="status-bar">
                <span class="dot"></span>
                <span class="status-text" id="status-text">Ready</span>
                <span id="autosave-countdown"></span>
            </div>
            <a class="btn-secondary"
               href="{{ route('schema.table', ['project' => $project->slug, 'database' => $database->name, 'table' => $table->name]) }}">
                <x-heroicon-o-arrow-left class="btn-icon-svg" /> Back to Table
            </a>
            <button class="btn-save" id="btn-manual-save" title="Save (Ctrl+S)">
                <x-heroicon-o-document-arrow-down class="btn-icon-svg" />
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
                <div class="section-title section-title-flex">
                    <span>Foreign Key</span>
                    <button class="btn-secondary btn-secondary-sm" id="btn-config-fk"><x-heroicon-o-cog-6-tooth class="btn-icon-svg" /> Configure</button>
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
        <h3 id="fk-dialog-title"><x-heroicon-o-link class="dialog-title-icon" /> Foreign Key Configuration</h3>
        <button class="btn-icon" id="fk-dialog-close"><x-heroicon-o-x-mark class="btn-icon-svg" /></button>
    </div>
    <div class="dialog-body">
        <div class="dialog-group">
            <label for="fk-select-table">Referenced Table</label>
            <select id="fk-select-table">
                <option value="">— None (remove FK) —</option>
                @foreach($allTables as $t)
                    @if(!($t->id == $table->id))
                        <option value="{{ $t->id }}" {{ $column->referenced_table_id === $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endif
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
    const icons = { success: 'M4.5 12.75l6 6 9-13.5', error: 'M6 18L18 6M6 6l12 12', info: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z' };
    el.innerHTML = `<svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="${icons[type] || 'M12 12h.01'}"/></svg><span>${msg}</span>`;
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
