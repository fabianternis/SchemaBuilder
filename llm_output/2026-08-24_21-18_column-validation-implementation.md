# SchemaBuilder — Column Validation: Implementation Log

**Date:** 2026-08-24 21:18  
**Author:** Antigravity (AI Coding Assistant)  
**Task:** Implement Option A from `2026-08-23_20-17_column-validation-options.md` — full column-state validation in backend AND frontend, with PHP as the single source of truth.

---

## What was implemented

### 1. `app/Support/ColumnTypeRules.php` *(new)*

The central single source of truth (SSOT). All rule logic lives here.

| Method | Purpose |
|---|---|
| `allows(string $type, string $option): bool` | Returns whether a given option is legal for the given type |
| `sanitise(array $attrs): array` | Strips illegal option values and fixes cross-field conflicts; safe to call anywhere |
| `asJson(): array` | Returns the rule map for serialisation into Blade / JS |

Rules enforced:
- `length` only allowed for: `varchar`, `string`, `char`, `decimal`, `float`, `double`
- `auto_increment` only allowed for integer-family types
- Primary key + nullable → nullable forced to `false`
- Auto-increment + nullable → nullable forced to `false`
- Primary key + unique → unique forced to `false` (redundant by definition)

---

### 2. `app/Models/SchemaColumn.php` *(updated)*

Added `booted()` saving observer:

```php
protected static function booted(): void
{
    static::saving(function (SchemaColumn $col) {
        $sanitised = ColumnTypeRules::sanitise($col->getAttributes());
        foreach ($sanitised as $key => $value) {
            $col->setAttribute($key, $value);
        }
    });
}
```

This is a last-resort guard. Every save — whether from a controller, an artisan command, a seeder, or tinker — passes through sanitisation. The observer silently fixes rather than throws (user-facing errors come from the Form Request).

Also removed the broken `can_be_nullable()` stub that was previously present.

---

### 3. `app/Http/Requests/UpdateColumnRequest.php` *(new)*

Form Request used by `updateColumn()`. Replaces the inline `$request->validate([…])`.

- Per-field type rules via `Rule::prohibitedIf(!ColumnTypeRules::allows($type, $option))`
- Cross-field rules via `withValidator()` after-hook
- Returns structured 422 JSON — the existing JS frontend already handles `data.errors`

---

### 4. `app/Http/Controllers/SchemaController.php` *(updated)*

- `updateColumn()` — signature changed from `Request` to `UpdateColumnRequest`; inline validate removed
- `updateTable()` — column `$attrs` array now wrapped in `ColumnTypeRules::sanitise([…])`
- Stale `ToDo` comment block removed
- Dead `getAvailableOptions()` method (and commented-out `validOptionsForType`) removed

---

### 5. `app/Services/DatabaseImportService.php` *(updated)*

Added private `buildColumnAttrs(array $raw): array` helper that wraps `ColumnTypeRules::sanitise()`.

All three import paths now route through it:
- `importSql()` — SQL `CREATE TABLE` parser
- `importJson()` — SchemaBuilder JSON export re-import
- `importCsv()` — CSV column definitions

Previously all three called `Column::create($attrs)` with completely raw parsed data.

---

### 6. `app/Services/DatabaseExportService.php` *(updated)*

All four export renderers now sanitise each column before rendering, so existing dirty DB rows produce valid output:

- `exportTableSql()` — `(object) ColumnTypeRules::sanitise($rawColumn->getAttributes())`
- `exportTableLaravel()` — same
- `tableToArray()` (JSON) — sanitised per-column in the map closure
- `exportTableCsv()` / `exportDatabaseCsv()` — same

All `// ToDo: add validation` comments removed.

---

### 7. `resources/views/schema/column.blade.php` *(updated)*

Frontend SSOT bridge:

```js
const COLUMN_TYPE_RULES = @json(\App\Support\ColumnTypeRules::asJson());
```

New JS functions:
- `typeAllows(type, option)` — mirrors `ColumnTypeRules::allows()` in JS
- `applyTypeConstraints(type)` — disables + clears the Length field and Auto-increment toggle for inapplicable types; runs on every `onChange()` and on page load
- `updateConflictHighlights()` — adds `.conflict` CSS class to toggle-cards in real time when PK+nullable, AI+nullable, or PK+unique conflicts exist
- `validateState(s)` — pre-save check; shows toast errors and aborts the fetch if invalid

---

### 8. `resources/views/schema/table.blade.php` *(updated)*

Same rules injected and same logic applied, but operating per-row (the table editor manages an array of column objects rather than a single state object):

- `typeAllows()` — shared helper (same as column view)
- `applyRowTypeConstraints(row, colObj)` — disables Length input and AI checkbox per row
- `updateRowConflicts(row, colObj)` — adds `.conflict` to `.toggle-row` elements
- `validateColState(col)` — validates a single column object; returns error strings with column name prefix
- `saveSchema()` — preflight: `columns.flatMap(col => validateColState(col))` — all errors shown as toasts before any fetch
- `attachRowListeners()` — now calls `applyRowTypeConstraints` + `updateRowConflicts` on every input/change
- `buildColumnRow()` — calls both after building, so new rows start with correct constraint state
- `initServerRows()` — calls both after attaching listeners, so server-rendered rows reflect constraints on page load

---

### 9. `public/app.css` *(updated)*

Two new utility states:

```css
/* .conflict — red border/text on mutually exclusive toggle combinations */
.toggle-card.conflict { border-color: #e55; background: color-mix(in srgb, #e55 8%, white); }
.toggle-row.conflict label:last-child { color: #c33; font-weight: 600; }

/* .field-disabled — greyed out, not interactive, for inapplicable options */
.toggle-card.field-disabled { opacity: 0.45; pointer-events: none; }
.exp-group.field-disabled, .toggle-row.field-disabled { opacity: 0.45; pointer-events: none; }
.form-group.field-disabled { opacity: 0.45; pointer-events: none; }
```

---

## Validation matrix

| Invalid state | Observer | Form Request | Import | Export | Frontend (disable) | Frontend (conflict) |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| `longText`/`text`/`tinyText` + `length` | ✅ strip | ✅ 422 | ✅ strip | ✅ strip | ✅ | — |
| `boolean`/`json`/`enum` + `length` | ✅ | ✅ | ✅ | ✅ | ✅ | — |
| Non-integer type + `auto_increment` | ✅ strip | ✅ 422 | ✅ strip | ✅ strip | ✅ | — |
| Primary key + nullable | ✅ fix | ✅ 422 | ✅ fix | ✅ fix | — | ✅ red |
| Auto-increment + nullable | ✅ fix | ✅ 422 | ✅ fix | ✅ fix | — | ✅ red |
| Primary key + unique (redundant) | ✅ strip | ✅ 422 | ✅ strip | ✅ strip | — | ✅ red |

---

## Files changed

```
app/Support/ColumnTypeRules.php               (new)
app/Http/Requests/UpdateColumnRequest.php     (new)
app/Models/SchemaColumn.php                   (updated)
app/Http/Controllers/SchemaController.php     (updated)
app/Services/DatabaseImportService.php        (updated)
app/Services/DatabaseExportService.php        (updated)
resources/views/schema/column.blade.php       (updated)
resources/views/schema/table.blade.php        (updated)
public/app.css                                (updated)
```

---

## Notes / potential follow-ups

- **Existing dirty data:** rows already in the DB with bad states won't be auto-cleaned until they are next saved through a form. A one-time Artisan command (`php artisan schema:sanitize-columns`) could clean them proactively.
- **`VALID_TYPES` list** in `SchemaColumn::$VALID_TYPES` and the `<select>` in both Blade views must be kept in sync manually when new types are added.
- **`Rule::prohibitedIf()`** in the Form Request returns 422 only for JSON requests — which is correct here since both endpoints return `response()->json()`.
