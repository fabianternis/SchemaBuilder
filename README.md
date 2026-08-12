# SchemaBuilder<br><img src="https://hackatime.hackclub.com/api/v1/badge/U0B8JTZDTKQ/fabianternis/SchemaBuilder" alt="Hackatime Badge">

## Info
SchemaBuilder is a web application built on the Laravel PHP framework, engineered for designing, managing, and structuring relational database schemas. 

### Project Context
SchemaBuilder is a project developed for [Macondo](https://macondo.hackclub.com/projects/13022), a [Hack Club](https://hackclub.com) program.

## Stack
* **Backend:** Laravel (PHP Framework).
* **Frontend:** Laravel Blade views (layouts, components, custom "resource templates").
* **Build System:** Native Laravel tooling. Node.js dependencies have been removed because the target webserver does not support it and to eliminate mass-package installation overhead.
* **Authentication:** Custom `AuthController` handling standard credentials. Supports login via both email and username, with schema provisions for future GitHub OAuth integration.

## Database Schema Structure
The database-structure of the DatabaseSchemaBuilder supports a nested "hirarchy"(or however it is called) that links databases withe their tables and the tables columns to a Project. The complete breakdown is in [SCHEMA.md](SCHEMA.md).

### Base Items
*   **Users (`users`):** System authenticators identified by UUID/ULID. Supports standard email verification and tracks session data (`sessions`, `password_reset_tokens`).
*   **Projects (`projects`):** High-level containers owned by users (polymorphic relationship via `owner_id` / `owner_type`). Stores metadata, deployment URLs, and JSON preferences.
*   **Databases (`schema_databases`):** Logical schema groups bound to a specific project. A default database is auto-generated upon project initialization.
*   **Tables (`schema_tables`):** Data structures belonging to a specific database.
*   **Columns (`schema_columns`):** Granular field definitions belonging to tables. Tracks data types, constraints (`is_nullable`, `is_primary`, `is_unique`, `auto_increment`), defaults, and foreign key references (`referenced_table_id`, `on_cascade`).

## Development Roadmap
The steps i took in the project (until some point). The complete log is in [ROADMAP.md](ROADMAP.md).

_// ToDo __
