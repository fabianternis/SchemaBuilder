# Codebase Review — Bug Fixes & Test Suite

**Date:** 2026-07-16 14:00  
**Model:** Claude Sonnet 4.6 (Thinking)  
**PromptID:** `10`

---

## Overview

Full senior-level review of the SchemaBuilder Laravel codebase. All functionality was verified, bugs were fixed, missing functionality was added, and a comprehensive test suite was written.

**Result: 48 tests / 103 assertions — all passing ✅**

---

## Bugs Fixed

### 🔴 Critical

**1. `AuthController::login()` — Session Fixation Vulnerability**  
`session()->regenerate()` was called on the *failure* path instead of the success path. This is a session fixation vulnerability — an attacker could fix a session ID before the user authenticates, then inherit their session.  
**Fix:** Moved `$request->session()->regenerate()` inside the `if(Auth::attempt(...))` block. Also added proper `withErrors()` on failure.

**2. `AuthController::logout()` — No Redirect After Logout**  
`logout()` called `Auth::logout()`, invalidated the session, then returned nothing. Users were left on a blank/error page.  
**Fix:** Added `return redirect()->route('auth.login')`.

**3. `SchemaController::createTable()` — PHP `compact()` Misuse**  
`compact([$project, $database])` was called with an **array of object instances** rather than an array of variable name strings. `compact()` requires string variable names. This would throw a `compact(): Argument #1 must be of type array|string, object given` error at runtime.  
**Fix:** Changed to `compact('project', 'database')`.

**4. `SchemaController::storeTable()` — Wrong Redirect Route Parameters**  
After storing a table, the redirect called `route('schema.table', compact(['project', 'database', 'table']))`. The route expects named parameters `project` (slug), `database` (name), `table` (name) — but the compact keys were the model instances, causing route generation to fail.  
**Fix:** Changed to an explicit array: `['project' => $project->slug, 'database' => $database->name, 'table' => $table->name]`.

**5. `routes/web.php` — Critical Route Ordering Bug (404 on export & _tables)**  
The literal sub-routes `/{database}/_tables` and `/{database}/export` were declared **after** the `/{database}/{table:name}` wildcard. Laravel matches routes in declaration order, so `_tables` and `export` were caught as `{table:name}` values, resulting in 404s (no table named `_tables` or `export` exists).  
**Fix:** Moved all literal segment routes (`_tables`, `export`, `new`) before the `{table:name}` wildcard.

**6. `routes/web.php` — Missing `schema.export` Route**  
The `SchemaController::export()` method existed but was never registered as a route. It was completely unreachable.  
**Fix:** Added `Route::get('/{project:slug}/{database:name}/export', ...)`.

---

### 🟠 Logic Errors

**7. `ProjectController::index()` — Returns All Projects (Data Leak)**  
`Project::all()` returned every project in the database, exposing other users' projects.  
**Fix:** Changed to `Project::where('owner_id', auth()->id())->get()`.

**8. `ProjectController` — Wrong Parameter Type for Route-Bound Methods**  
`show()`, `edit()`, `update()`, `destroy()` all accepted `string $id` and used `findOrFail($id)`, but the routes use `{project:slug}` which performs route-model binding by slug. The slug is not the primary key, so `findOrFail()` would always fail.  
**Fix:** Changed all four method signatures to accept `Project $project` (route-model binding).

**9. `ProjectController::store()` — No Slug Auto-Generation**  
When a user submitted the create-project form without filling in the slug field, `slug` was stored as `null`. Subsequent routes that reference `{project:slug}` would then fail entirely.  
**Fix:** Added the same slug auto-generation logic (with uniqueness counter) that `update()` and `quickStore()` already had.

**10. `SchemaController` API Methods — No Authorization Checks**  
`updateTable()`, `updateColumn()`, `tablesList()`, and `export()` were using raw string parameters + manual `firstOrFail()` queries (some were missing ownership checks entirely). Any authenticated user could modify another user's schema.  
**Fix:** Converted all methods to route-model binding and added `abort_if($project->owner_id !== auth()->id(), 403)` on every entry point, plus parent-child relationship checks (`abort_if($database->project_id !== $project->id, 404)`).

**11. `SchemaController::storeTable()` — Global Table Name Uniqueness Check**  
The duplicate-name resolution loop used `Table::where('name', $name)->exists()` — checking globally across all databases. A table named `users` in another project would cause the new table to be renamed unnecessarily.  
**Fix:** Scoped to `->where('database_id', $database->id)`.

**12. `SchemaController::quickCreate()` — Broken Optional Model Binding**  
The method signature was `quickCreate(?Project $project)` on a route `/new/{project?}`. When no project slug is provided, Laravel tries to resolve `null` via model binding which can behave unexpectedly. Also there was no ownership check.  
**Fix:** Changed to accept `Request $request` and manually resolve the optional project from the route parameter with an ownership check.

---

### 🟡 Minor Issues

**13. `UserFactory` — Non-Existent `name` Field**  
The factory set `'name' => fake()->name()`, but the `User` model has no `name` column — it has `username`. This would cause all factory-created users to fail with a mass-assignment or DB error.  
**Fix:** Changed to `'username' => fake()->unique()->userName()`.

**14. Migration `down()` — Empty Rollback for `order_index`**  
The `2026_07_14_..._add_order_index_to_schema_columns_table.php` migration had an empty `down()` method. Running `migrate:rollback` would silently succeed without actually removing the column.  
**Fix:** Added `$table->dropColumn('order_index')`.

**15. All Schema Models — Missing `HasFactory` Trait**  
`Project`, `SchemaDatabase`, `SchemaTable`, `SchemaColumn` all lacked the `HasFactory` trait. Calling `Model::factory()` would throw `BadMethodCallException: Call to undefined method`.  
**Fix:** Added `use HasFactory` and the corresponding import to all four models.

**16. `Pest.php` — `RefreshDatabase` Commented Out**  
The `->use(RefreshDatabase::class)` line was commented out, meaning tests ran against whatever state the database was in rather than a clean slate. Also, the Unit test suite had no `RefreshDatabase` at all.  
**Fix:** Enabled for Feature tests; added `->use(RefreshDatabase::class)` for Unit tests too (since the service tests require DB access).

---

## Files Created

### Factories (new)
- `database/factories/ProjectFactory.php`
- `database/factories/SchemaDatabaseFactory.php`
- `database/factories/SchemaTableFactory.php`
- `database/factories/SchemaColumnFactory.php`

### Tests (new)
| File | Tests | Coverage |
|------|-------|----------|
| `tests/Feature/AuthTest.php` | 17 | Login, signup, logout — success & failure cases |
| `tests/Feature/ProjectTest.php` | 9 | Full CRUD, auth isolation, soft-delete |
| `tests/Feature/SchemaTest.php` | 14 | Quick-create, show all levels, JSON API, export |
| `tests/Unit/DatabaseExportServiceTest.php` | 7 | SQL export: columns, constraints, FK, auth guard |

### Modified
- `routes/web.php` — Fixed ordering, added export route, fixed projects.show closure
- `app/Http/Controllers/AuthController.php` — Fixed login/logout
- `app/Http/Controllers/ProjectController.php` — Fixed all methods + auto-slug
- `app/Http/Controllers/SchemaController.php` — Full rewrite with proper auth + binding
- `app/Models/Project.php` — Added HasFactory
- `app/Models/SchemaDatabase.php` — Added HasFactory
- `app/Models/SchemaTable.php` — Added HasFactory
- `app/Models/SchemaColumn.php` — Added HasFactory
- `database/factories/UserFactory.php` — Fixed `name` → `username`
- `database/migrations/2026_07_14_..._add_order_index.php` — Fixed `down()`
- `tests/Pest.php` — Enabled RefreshDatabase, added `loginUser()` helper
- `tests/Feature/ExampleTest.php` — Updated to match actual app behaviour

---

## Open Notes

- The `SchemaController::index()` method (`schema.index`) has no matching route in `web.php` and appears to be vestigial. Consider removing or wiring it up.
- `DatabaseExportService::exportDatabase()` accepts a `$to` format parameter but ignores it — only SQL is implemented. The parameter is dead code.
- The `resources.show` route (`projects.show`) redirects to the schema view. The `ProjectController::show()` method renders `resources.show` which is still accessible if navigated to directly — this dual-path is intentional per the project design but worth documenting.
