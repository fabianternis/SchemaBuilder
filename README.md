# SchemaBuilder<br><img src="https://hackatime.hackclub.com/api/v1/badge/U0B8JTZDTKQ/fabianternis/SchemaBuilder" alt="Hackatime Badge">

## Info
SchemaBuilder is a web application built on the Laravel PHP framework, engineered for designing, managing, and structuring relational database schemas. 

### Project Context
SchemaBuilder is a project developed for [Macondo](https://macondo.hackclub.com/projects/13022), a [Hack Club](https://hackclub.com) program.

## Stack
* **Backend:** Laravel (PHP Framework).
* **Frontend:** Laravel Blade views (layouts, components, custom "resource templates").
* **Build System:** Native Laravel tooling. Node.js dependencies have been removed because the webserver does not support it (and becuase i am not a fan of mass-package-installations).
* **Authentication:** Custom `AuthController` handling standard credentials. Supports login via both email and username, with schema support for future GitHub OAuth integration.

## Database Schema Structure
The database-structure of the DatabaseSchemaBuilder supports a nested "tree" that links databases withe their tables and the tables columns to a Project. The complete breakdown is in [SCHEMA.md](SCHEMA.md).

### Base Items
*   **Users (`users`):** identified by ULIDs, Supports standard email verification and tracks session data (`sessions`, `password_reset_tokens`).
*   **Projects (`projects`):** High-level containers owned by users (morph relationship via `owner_id` / `owner_type`). Stores metadata, deployment URLs, and JSON preferences(not really used *yet*).
*   **Databases (`schema_databases`):** Logical schema groups bound to a specific project. A default database is auto-generated upon project creation.
*   **Tables (`schema_tables`):** Data structures belonging to a specific database.
*   **Columns (`schema_columns`):** Granular field definitions belonging to tables. Tracks data types, constraints (`is_nullable`, `is_primary`, `is_unique`, `auto_increment`), defaults, and foreign key references (`referenced_table_id`, `on_cascade`). Belongs to table and optionally to another "foreign" table.

## What is this even and what is it for?
SchemaBuilder is a Application that allows you to design your Database-Schema while maintaining a better overview that you may have when just making migrations without a clear plan.
It is a user-friendly(at least for me (becuase i made it as i wanted it)) Application that links columns, tables and databases (also <!--foreign-keys and -->foreign-tables) and allows for "Just a better Scheming Experience"(whatever the verb is for designing database-schemas).
This is not a application you would use, creating a simple users-table or a minimalistic todo-list (while you could).
This is for more complex applications and also quick testing (becuase you can export your schema to SQL or laravel-migrations with one click).

## Application Use Documentation
Just the very **Basics**
### Dashboard
After registering/authenticating in any way, you will end-up on the `/dashboard`-url where you will see the following interface
<img width="957" height="287" alt="Screenshot 2026-08-12 at 12 12 51" src="https://github.com/user-attachments/assets/7990eca3-f037-4b3c-801b-6df98cf143bc" /><br>
On there you have the options to:
- open projects-list
- Create new database(and optionally also a Project)
- and log-out (of course)

### Database Creation
When Clicking "New Database" on the Dashboard, you ill end-up on a form.
<img width="957" height="607" alt="Screenshot 2026-08-12 at 12 14 31" src="https://github.com/user-attachments/assets/b8fb992b-bb64-43ae-9f7c-9b3a6c431e18" /><br>
If you already have Projects, they will be listed in the "Project"-input.
If "-- Create New --" is selected and something is put in the "Project Name" input below, a new project will be created and the database will be assigned to it. If you select an existing Project, the database will be linked to that Project.

### Database Overview
After Creating a database (or just selecting the database from the Project-page) - there are multiple ways to get here
<img width="957" height="852" alt="Screenshot 2026-08-12 at 12 22 42" src="https://github.com/user-attachments/assets/05e7c365-6f8b-4eb3-abad-60891c025f8f" /><br>
On here, you can import schema from SQL, JSON and CSV (which are also export-options on the application).

### Table Overview
After creating a table or clicking on a table, you end up here.
<img width="957" height="603" alt="Screenshot 2026-08-12 at 12 26 33" src="https://github.com/user-attachments/assets/deb584d5-8d91-44d2-8dd6-7df2c03eb881" />

By clicking on "Add Column", you add a new item to the "Columns Container". when anything changes on the "Columns Container", after 10 seconds of nothing changing, The changes get auto-daved to the database. (there may be settings to change this in the future).

### Schema Export
When you are ready to export your Schema, you can just go back to the "database overview".
<img width="957" height="761" alt="Screenshot 2026-08-12 at 12 34 51" src="https://github.com/user-attachments/assets/573ca58a-f5f0-45d6-b826-c1a348b32bc1" />
On there you have the options to export the schema as:
- SQL ("Code")
- Laravel Migration (The main PHP framwork, i use and the reason, this application even exists)
- JSON
- CSV


## Development Roadmap
The steps i took in the project (until some point) can be found in [ROADMAP.md](ROADMAP.md).

## Inspiration
I searched for a solution to design database-schemas for my Projects easily while maintaining an "overlook".
I was not successful finding one, so i decided to create a Schema-designing application myself.


## Host Yourself
### what you need
- Server that supports PHP
- Optionally(recommended): MySQL/MarinaDB Database (PostgreSQL should also work)
- ssh-access to the server

### steps
- Clone the repo to the server <br> example: `git clone https://github.com/fabianternis/schemabuilder.git .`(when inside the "project folder")
- `cp .env.example .env`
- **IF Using a hosted database**: <br> `nano .env` and add db-credentials (just open .env in some way and add the db-credentials)
- `composer install--no-dev && php artisan key:generate --force && php artisan migrate:fresh --seed --force`
- Set the "host base-path" to the `public/`-folder of the project-folder
