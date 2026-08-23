# SchemaBuilder — Column Validation: Options & Best Practices

**Date:** 2026-08-23 20:17  
**Author:** Antigravity (AI Coding Assistant)  
**Task:** Add validation for "invalid column states" (e.g. `largeText` with a length set, nullable primary key) in both frontend AND backend, including on import.

---

## 0. The Problem in Plain Terms

A `SchemaColumn` has a `type` plus a bunch of option fields (`length`, `is_nullable`, `is_primary`, `is_unique`, `auto_increment`, …). Not every combination is legal. Currently nothing prevents saving:

| Invalid state | Why it's wrong |
|---|---|
| `longText` / `mediumText` / `tinyText` + `length` set | TEXT variants have no length parameter in MySQL/PostgreSQL |
| `is_primary = true` + `is_nullable = true` | A primary key can never be NULL |
| `is_primary = true` + `is_unique = true` | Redundant — PKs are unique by definition |
| `boolean` + `length` | Booleans have no length |
| `enum` + `auto_increment` | No AUTO_INCREMENT on enum |
| `auto_increment = true` + `is_nullable = true` | AUTO_INCREMENT columns are implicitly NOT NULL |
| `timestamps` / `softDeletes` / `id` (Laravel magic types) + any manual modifier | These are Laravel shorthands that manage everything internally |
| `foreignId` + no `referenced_table_id` | The whole point of a foreignId is to carry a FK |

The commented-out `getAvailableOptions()` skeleton in `SchemaController` (lines 350–397) is the right instinct — it just was never wired up and its data structure was inconsistent.

---

## 1. Core Principle: A Single Source of Truth (SSOT)

**Best practice: define the rules once, share them everywhere.**

The worst outcome is separate rule-sets in JS and PHP that drift out of sync. The goal is one PHP class/array that:
- drives backend `FormRequest` validation
- drives the `SchemaColumn` model observer (sanitise before save)
- drives the import service (sanitise imported data)
- gets serialised to JSON and injected into every Blade view that has column editing

---

## 2. Where to Define the Rules

### Option A — Static array on `SchemaColumn` (simplest)

```php
// app/Models/SchemaColumn.php

public const VALID_TYPES = [
    'bigint', 'bigIncrements', 'binary', 'boolean', 'char', 'date',
    'dateTime', 'decimal', 'double', 'enum', 'float', 'foreignId',
    'id', 'integer', 'json', 'jsonb', 'longText', 'mediumInteger',
    'mediumText', 'nullableTimestamps', 'smallInteger', 'softDeletes',
    'string', 'text', 'time', 'timestamp', 'timestamps', 'tinyInteger',
    'tinyText', 'unsignedBigInteger', 'unsignedInteger', 'uuid', 'ulid', 'year',
    // … keep in sync with the <select> in column.blade.php
];

/**
 * Per-option: which types ALLOW that option?
 * null  = allowed for ALL types
 * []    = allowed for NO types
 * [..] = allowed only for the listed types
 */
public static array $allowedOptions = [
    'length' => [
        'varchar', 'string', 'char', 'decimal', 'float', 'double',
        // NOT: text, longText, mediumText, tinyText, boolean, enum, json, …
    ],
    'auto_increment' => [
        'integer', 'int', 'bigint', 'bigIncrements',
        'unsignedBigInteger', 'unsignedInteger',
        'smallInteger', 'mediumInteger', 'tinyInteger',
    ],
    'is_primary'  => null,  // allowed on any type (but cross-rule: not nullable)
    'is_unique'   => null,
    'is_nullable' => null,
    'default'     => null,  // soft-warn for some types, but don't hard-block
    'referenced_table_id' => [
        'foreignId', 'unsignedBigInteger', 'unsignedInteger',
        'integer', 'bigint', 'uuid', 'ulid',
    ],
];
```

**Pros:** one file, zero dependencies, serialisable with `json_encode()`.  
**Cons:** needs manual updates when new types are added. The SSOT for the type list is the `<select>` in the Blade view — keep them in sync.

---

### Option B — Dedicated `ColumnTypeRules` class (recommended for this project)

```php
// app/Support/ColumnTypeRules.php

class ColumnTypeRules
{
    /** Returns true if $option is allowed for $type. */
    public static function allows(string $type, string $option): bool
    {
        $allowed = SchemaColumn::$allowedOptions[$option] ?? null;
        return $allowed === null || in_array(strtolower($type), array_map('strtolower', $allowed));
    }

    /** Strip options that are illegal for the given type. Returns cleaned attribute array. */
    public static function sanitise(array $attrs): array
    {
        $type = $attrs['type'] ?? 'varchar';

        if (!self::allows($type, 'length'))         $attrs['length']         = null;
        if (!self::allows($type, 'auto_increment'))  $attrs['auto_increment'] = false;

        // Cross-field rules
        if (($attrs['is_primary'] ?? false) && ($attrs['is_nullable'] ?? false)) {
            $attrs['is_nullable'] = false;   // or: throw a ValidationException
        }

        return $attrs;
    }

    /** Serialise for frontend injection. */
    public static function asJson(): array
    {
        return SchemaColumn::$allowedOptions;
    }
}
```

**Why preferred:** the controller, Form Request, model observer, and import service all call `ColumnTypeRules::*` — the rules stay centralised even as they're used in multiple places.

---

### Option C — PHP Enum with `allowedOptions()` method

```php
enum ColumnType: string
{
    case VARCHAR  = 'varchar';
    case LONGTEXT = 'longText';
    // …

    public function allowedOptions(): array
    {
        return match($this) {
            self::VARCHAR  => ['length', 'is_nullable', 'is_primary', 'is_unique', 'default'],
            self::LONGTEXT => ['is_nullable', 'is_unique', 'default'],  // no length!
            // …
        };
    }
}
```

**Pros:** IDE auto-complete, exhaustiveness checks in PHP 8.1+, type-safe.  
**Cons:** most verbose; every new type requires updating the enum; harder to serialise to JSON for the frontend.

---

### Option D — `config/column_types.php` file

Move the rules into a Laravel config file so they can be published/overridden without touching model code.

**Pros:** follows Laravel conventions for shareable config.  
**Cons:** overkill for an app that won't be distributed as a package; slightly more awkward to access from JS.

---

## 3. Backend: Where to Enforce

### 3a. Laravel Form Request (primary enforcement)

Extract from the fat controller into dedicated `UpdateColumnRequest` / `UpdateTableRequest`:

```php
// app/Http/Requests/UpdateColumnRequest.php

class UpdateColumnRequest extends FormRequest
{
    public function rules(): array
    {
        $type = strtolower($this->input('type', ''));

        return [
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'string', Rule::in(SchemaColumn::VALID_TYPES)],
            'length'         => [
                'nullable', 'integer', 'min:1',
                Rule::prohibitedIf(!ColumnTypeRules::allows($type, 'length')),
            ],
            'auto_increment' => [
                'boolean',
                Rule::prohibitedIf(!ColumnTypeRules::allows($type, 'auto_increment')),
            ],
            'is_nullable'    => ['boolean'],
            'is_primary'     => ['boolean'],
            'is_unique'      => ['boolean'],
            'default'        => ['nullable', 'string', 'max:255'],
            'on_cascade'     => ['nullable', 'string', 'max:50'],
            'referenced_table_id' => ['nullable', 'string'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($this->boolean('is_primary') && $this->boolean('is_nullable')) {
                $v->errors()->add('is_nullable', 'A primary key column cannot be nullable.');
            }
            if ($this->boolean('auto_increment') && $this->boolean('is_nullable')) {
                $v->errors()->add('is_nullable', 'AUTO_INCREMENT columns cannot be nullable.');
            }
            if ($this->boolean('is_primary') && $this->boolean('is_unique')) {
                $v->errors()->add('is_unique', 'Primary keys are unique by definition; is_unique is redundant.');
            }
        });
    }
}
```

Then in the controller replace the inline `$request->validate([…])` calls:

```php
public function updateColumn(UpdateColumnRequest $request, …)
{
    $validated = $request->validated();
    $column->update($validated);
    // …
}
```

**Why a Form Request over inline `validate()`?**
- Reusable across `updateColumn()` and the `foreach` loop in `updateTable()`.
- Testable in isolation.
- Keeps controllers thin.
- Laravel auto-returns a 422 JSON response with structured `errors` — which the existing JS frontend already handles (`data.errors`).

---

### 3b. Eloquent Model Observer / `saving` event (defence in depth)

Even after a Form Request, add a model-level safety net so no code path (artisan tinker, seeder, direct model call) can write illegal data:

```php
// In SchemaColumn::booted()

protected static function booted(): void
{
    static::saving(function (SchemaColumn $col) {
        $type = strtolower($col->type ?? '');

        if (!ColumnTypeRules::allows($type, 'length')) {
            $col->length = null;
        }
        if (!ColumnTypeRules::allows($type, 'auto_increment')) {
            $col->auto_increment = false;
        }
        if ($col->is_primary && $col->is_nullable) {
            $col->is_nullable = false; // silently fix, or throw
        }
    });
}
```

**Important:** the observer should **silently sanitise**, not throw. Throwing from an observer is confusing for the caller. Let the Form Request throw the user-facing error; the observer is the last-resort guardrail.

---

### 3c. Import-time sanitisation (addresses ToDo #6)

Both `importSql()`, `importJson()`, and `importCsv()` in `DatabaseImportService` call `Column::create($attrs)` with raw parsed data — no type-rule checks. Wrap column creation through a shared helper:

```php
// In DatabaseImportService

private function buildColumnAttrs(array $raw): array
{
    return ColumnTypeRules::sanitise([
        'table_id'            => $raw['table_id'],
        'name'                => $raw['name'],
        'type'                => $raw['type'] ?? 'varchar',
        'length'              => $raw['length'] ?? null,
        'is_nullable'         => $raw['is_nullable'] ?? false,
        'is_primary'          => $raw['is_primary'] ?? false,
        'is_unique'           => $raw['is_unique'] ?? false,
        'auto_increment'      => $raw['auto_increment'] ?? false,
        'default'             => $raw['default'] ?? null,
        'on_cascade'          => $raw['on_cascade'] ?? null,
        'referenced_table_id' => $raw['referenced_table_id'] ?? null,
        'order_index'         => $raw['order_index'] ?? 0,
    ]);
}

// Then replace every Column::create($attrs) with:
Column::create($this->buildColumnAttrs($attrs));
```

---

### 3d. Export sanitisation (addresses ToDo `// ToDo: Add validation` comments)

`DatabaseExportService` has multiple `// ToDo: add validation` comments. Rather than duplicating logic, call `ColumnTypeRules::sanitise()` as you iterate:

```php
// In exportTableSql(), exportTableLaravel(), tableToArray()

foreach ($columns as $column) {
    $safe = (object) ColumnTypeRules::sanitise((array) $column->getAttributes());
    // use $safe->length, $safe->auto_increment, etc. instead of $column->…
}
```

This means even if existing DB rows have bad state, the exported output is always valid.

---

## 4. Frontend: Where to Enforce

The frontend has two editing surfaces:
1. **`column.blade.php`** — single column, vanilla JS autosave
2. **`table.blade.php`** — inline table editor with rows of columns

Both need the same logic.

### 4a. Inject rules from PHP (SSOT bridge)

At the top of each `@section('scripts')` block:

```js
const COLUMN_TYPE_RULES = @json(\App\Support\ColumnTypeRules::asJson());
// e.g. { "length": ["varchar","string","char",...], "auto_increment": [...], ... }
```

This way there is **no separate JS rules file** to maintain. The PHP array is the source of truth; the frontend reads it at page load.

### 4b. Disable/hide inapplicable controls on type change

```js
function applyTypeConstraints(type) {
    const allowed = {};
    for (const [option, types] of Object.entries(COLUMN_TYPE_RULES)) {
        allowed[option] = types === null || types.includes(type);
    }

    // Length field
    colLength.disabled = !allowed.length;
    colLength.closest('.form-group').classList.toggle('field-disabled', !allowed.length);
    if (!allowed.length) colLength.value = '';

    // Auto-increment toggle
    toggleAI.disabled = !allowed.auto_increment;
    toggleAI.closest('.toggle-card').classList.toggle('field-disabled', !allowed.auto_increment);
    if (!allowed.auto_increment && toggleAI.checked) {
        toggleAI.checked = false;
        toggleAI.dispatchEvent(new Event('change'));
    }
}

colType.addEventListener('change', () => applyTypeConstraints(colType.value));
applyTypeConstraints(colType.value); // run on page load too
```

**Design note: disable vs. hide?**
- **Disable** (greyed out) = user can see the option exists but knows it's not applicable → better for learning.
- **Hide** = cleaner for experienced users.
- Good middle ground: hide with a CSS transition, add a small `(?)` tooltip explaining why.

### 4c. Cross-field validation before save

Extend the existing `saveColumn()` function — add a `validateState()` pre-flight:

```js
function validateState(s) {
    const errors = [];
    const typeRules = {};
    for (const [opt, types] of Object.entries(COLUMN_TYPE_RULES)) {
        typeRules[opt] = types === null || types.includes(s.type);
    }

    if (!s.name?.trim())
        errors.push('Column name is required.');
    if (s.is_primary && s.is_nullable)
        errors.push('A primary key column cannot be nullable.');
    if (s.auto_increment && s.is_nullable)
        errors.push('AUTO_INCREMENT columns cannot be nullable.');
    if (s.is_primary && s.is_unique)
        errors.push('is_unique is redundant on a primary key — consider removing it.');
    if (s.length && !typeRules.length)
        errors.push(`Length is not supported for type "${s.type}".`);

    return errors;
}

async function saveColumn() {
    readState();
    const errors = validateState(state);
    if (errors.length) {
        errors.forEach(e => toast(e, 'error'));
        return;
    }
    // … existing fetch logic unchanged
}
```

### 4d. Real-time visual conflict indicators

Beyond toasts on save, add immediate visual feedback when the user creates a conflict:

```js
function updateConflictHighlights() {
    const isPK = togglePrim.checked;
    const isNullable = toggleNull.checked;

    const conflict = isPK && isNullable;
    toggleNull.closest('.toggle-card').classList.toggle('conflict', conflict);
    togglePrim.closest('.toggle-card').classList.toggle('conflict', conflict);
}

togglePrim.addEventListener('change', updateConflictHighlights);
toggleNull.addEventListener('change', updateConflictHighlights);
```

Add a `.conflict` CSS rule (e.g. red left-border on `.toggle-card`) in `app.css`.

---

## 5. Recommended Implementation Order

| # | What | Files |
|---|---|---|
| 1 | Create `ColumnTypeRules` with the type→option map + `sanitise()` + `asJson()` | `app/Support/ColumnTypeRules.php` |
| 2 | Add `SchemaColumn::booted()` observer to auto-strip bad options on save | `app/Models/SchemaColumn.php` |
| 3 | Create `UpdateColumnRequest` Form Request with per-field + cross-field rules | `app/Http/Requests/UpdateColumnRequest.php` |
| 4 | Use the Form Request in `updateColumn()` and `updateTable()` | `SchemaController.php` |
| 5 | Thread `buildColumnAttrs()` through all three import paths | `DatabaseImportService.php` |
| 6 | Call `ColumnTypeRules::sanitise()` in export rendering loops | `DatabaseExportService.php` |
| 7 | Inject `COLUMN_TYPE_RULES` into Blade; add `applyTypeConstraints()` + `validateState()` | `column.blade.php`, `table.blade.php` |
| 8 | Add `.conflict` CSS + `updateConflictHighlights()` real-time feedback | `public/app.css`, same JS |

---

## 6. Potential Pitfalls

- **Existing dirty data:** once the observer sanitises on save, records already in the DB with bad states won't be auto-cleaned. Consider a one-time Artisan command `php artisan schema:sanitize-columns` that runs `ColumnTypeRules::sanitise()` over all existing `SchemaColumn` records and calls `save()`.

- **Type name casing:** the codebase mixes `longText`, `longtext`, `LONGTEXT` across import/export/model. Normalise to lowercase (or a canonical casing) inside `ColumnTypeRules::allows()` before any lookup — e.g. `strtolower($type)`.

- **`table.blade.php` complexity:** this view manages an *array* of column state objects in JS, not a single `state`. The same `applyTypeConstraints()` and `validateState()` logic must be applied per-row. Extract them as standalone functions so they're callable with any `{type, length, is_nullable, …}` object rather than reading global DOM directly.

- **`Rule::prohibitedIf()` returns 422 only for JSON requests.** Since `updateColumn` and `updateTable` are already JSON API endpoints (they return `response()->json()`), and the existing JS checks `data.errors`, this works out of the box. For any future Blade form submissions, use `redirect()->back()->withErrors()` instead.

- **`getAvailableOptions()` in `SchemaController`** (lines 350–397) can be deleted once `ColumnTypeRules` exists — it was the original attempt but was never finished or called.
