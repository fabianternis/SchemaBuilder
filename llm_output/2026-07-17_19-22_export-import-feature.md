# SchemaBuilder — Export/Import Feature Review & Findings

**Date:** 2026-07-17 19:22:30  
**Author:** Antigravity (AI Coding Assistant)  
**Task:** Add export dropdown, extend export formats, add import feature

---

## 1. What was changed

### Backend

| File | Change |
|---|---|
| `app/Services/DatabaseExportService.php` | Added **JSON** and **CSV** export formats. Extracted SQL and Laravel logic into private methods. Added `getMimeType()`, `getExtension()` helpers and a static `$targets` map. |
| `app/Services/DatabaseImportService.php` | **New file.** Implements SQL, JSON, CSV import. Parses `CREATE TABLE` statements with regex, resolves FK references by name/ID, upserts tables and columns. |
| `app/Http/Controllers/SchemaController.php` | `export()` now reads the `{to}` route segment and dispatches any valid format. New `import()` method validates the uploaded file, delegates to `DatabaseImportService`, and redirects with a flash message. |
| `routes/web.php` | Added `POST /{project}/{database}/import` route named `schema.import`. |

### Frontend

| File | Change |
|---|---|
| `resources/views/schema/database.blade.php` | Replaced the single Export link with an **animated dropdown** (SQL / Laravel / JSON / CSV). Added flash message banners (success + error). Added **Import Schema** section with format selector, drag-and-drop file area, and JS for interactivity. |
| `public/app.css` | Added styles for `.flash-*`, `.export-dropdown`, `.export-dropdown-menu`, `.export-icon-*`, `.import-form`, `.import-file-area` (drag-over state), and related helpers. |

---

## 2. Export formats

| Key | Description | File extension | MIME type |
|---|---|---|---|
| `sql` | `CREATE TABLE` DDL (MySQL-compatible) | `.sql` | `text/plain` |
| `laravel` | Laravel anonymous migration class | `.php` | `text/plain` |
| `json` | SchemaBuilder JSON schema (tables + columns) | `.json` | `application/json` |
| `csv` | Flat column-definition spreadsheet | `.csv` | `text/csv` |

URLs follow the existing pattern: `GET /{project}/{database}/export/{to}`.

---

## 3. Import feature

- **Endpoint:** `POST /{project}/{database}/import`  
- **Input:** multipart form with `from` (format key) + `schema` (uploaded file, max 2 MB)  
- **Behaviour:** tables are upserted (matched by name); columns are merged. No data is deleted on import — it is always additive. Foreign-key references within the same database are resolved by table name.
- **SQL parsing strategy:** regex-based (`CREATE TABLE … (…);` pattern) — handles backtick-quoted names, inline `PRIMARY KEY`, `DEFAULT`, `NULL/NOT NULL`, `AUTO_INCREMENT`, `UNIQUE`.

---

## 4. Findings & observations

### A. Export route had a hardcoded `'sql'` format
The original `export()` method always called `exportDatabase($database, 'sql')` regardless of the `{to?}` route segment that already existed in `web.php`. The route was wired up but the controller ignored it.

### B. No MIME types or file-extension mapping existed
The service had no metadata about what to put in `Content-Type` or the filename extension. Added a `static $targets` map as a single source of truth shared between the controller and (potentially) the view.

### C. The `mimes` validation rule for imported files
Laravel's `mimes` rule checks the true file extension **and** MIME type. SQL files saved as `.sql` are commonly typed `text/plain`. Added `mimes:txt,sql,json,csv,plain` to cover common platform variations. If this causes issues for some OS/browser combinations, replacing it with `file` + manual extension check is recommended.

### D. Import merges, never destructive
The import is intentionally **additive**. If a table with the same name already exists, new columns are appended; existing columns with the same name are updated. This prevents accidental data loss when re-importing a modified schema.

### E. FK resolution is best-effort in SQL import
Because `CREATE TABLE` statements reference tables by name and the import file may list tables in any order, FK resolution uses `referenced_table_id = null` for tables that don't exist yet in the database at import time. A future improvement could do a two-pass import (first create tables, then resolve FKs).

### F. Large SQL files
The import size is capped at 2 MB by Laravel's `max:2048` file validation rule. For very large schemas this could be raised in `config/filesystems.php` or the validation rule directly.

### G. No `timestamps()` shorthand in Laravel export
The Laravel migration exporter doesn't detect/emit `$table->timestamps()`. If a table has both `created_at` and `updated_at` timestamp columns, they will be emitted individually rather than via the shorthand. This is cosmetically suboptimal but functionally correct.

---

## 5. Potential future work

- **ERD diagram export** (Mermaid / PlantUML) — the data model already supports FK relationships.
- **PostgreSQL DDL export** — the SQL exporter currently emits MySQL syntax (backticks, `AUTO_INCREMENT`).
- **Import via URL** (fetch remote `.sql`/`.json` and parse server-side).
- **Undo import** — track which tables/columns were created during an import session.
- **Two-pass SQL import** for correct FK resolution.
- **Table-level export** — export a single table rather than the whole database.
