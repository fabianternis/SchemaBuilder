# A detailed List of Prompts that i gave to my AI-Agent

### 2026-07-11

[NEW SESSION]
`please update SCHEMA.md by adding the 'sessions'- and 'password_reset_tokens'-table from teh very first migration. then: update tzhe migration and create a new migration(and model) for Project(s)`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `1`
<hr>

[NEW SESSION]
`Review routes/web.php. Based on thr routing, i prepared, create all the Controllers neccessary and some very simple views. Also create the schema-routing.`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `3`
<hr>

[SESSION Continues]
`I did not provide enough context relating teh schema. I wanted all shcema-related models to be handled within the smae routing-group and Controller (the routes may even be e.g. "/{project_slug}/{database_name}/{table_name}/{column_name}") ... please rework that and update the Controller, routing and the schema-views.`
Model: `Gemini 3.5 Pro (High reasoning`
PromptID: `4`
<hr>

### 2026-07-12

[NEW SESSION]
`Please review the Project-model (I added $schema which i used to auto-genrate resource-views and validfation logic.). Please review the first two functions (create() and index()) of the ProjectController and the resources.index as well as resources.create views. Then finish the ProjectController and create the resources.show as well as resources.edit views (ALL BASED on the $schema inside the Project-model). If you have any fdeedback regarding the $schema please create a file as llm_output/{date+time}_{slug}.md.`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `5`
<hr>

[SESSION Continues]
`Please perofmr your feedback (except 5.) which i will implement something special for, myself (also removbe "preferences" from the $schema on Project). Then: Update the stzore() (and maybe updat()), regarding the morph "owber" (owner_type seems not to get set on db-quirey) (""[Error LOG (190+ Lines)]"")`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `6`
<hr>

### 2026-07-13

[NEW SESSION]
```You are an expert Laravel/PHP developer.. This is a Laravel application called "SchemaBuilder", used for designing database schemas.
Your Objective is to Refactor the views schema.column and schema.table into dynamic, JavaScript-driven interfaces that manage form state locally and synchronize with the backend via JSON payloads.



1. Review routes/web.php and app/Http/Controllers/SchemaController.php to understand the current routing, data structures, and expected backend payloads.
2. Rewrite the views (schema.column, schema.table). Remove standard HTML form submissions.
3. Implement a Vanilla JavaScript state-management approach. The entire view must function as a single reactive interface.
4. Implement UI enhancements: Use native HTML dialogs or basic CSS/JS popovers for complex configurations (e.g. configuring foreign keys or column modifiers) without leaving the main form view.





---





- State Synchronization: The JS must collect all inputs and construct a comprehensive, nested JSON representation of the current schema state.
- Save Mechanism 1 (Manual): Provide a global "Save Schema" button that triggers a POST/PUT request with the full JSON payload.
- Save Mechanism 2 (Auto-save): Implement a debounced auto-save function. If the JSON state changes, wait for exactly 12345ms (or just 10000ms) of user inactivity, then automatically send the request.
- Error Handling: Ensure UI feedback mechanisms exist for successful saves, pending saves, and validation errors returned from the SchemaController.
```
Model: `Claude Sonnet 4.6 (Thinking)`
PromptID: `7`
<hr>

### 2026-07-14

[NEW SESSION]
```
I added 'order_index' to schema_columns-table (to order columns on schema.table-view).

You are an expert Laravel/PHP developer
Please go into views: schema.column and schema.table and remove the CSS.
I want to create the entire CSS myself, so please keep (and maybe add) some classes.
Then install blade-icons via "composer require blade-ui-kit/blade-icons" and "php artisan vendor:publish --tag=blade-icons" and  implement the "ordering"-feature on schema.table view (also update the icons on the views ands IN NO CASE use emojis).
After that is finished add breadcrombs to all other "authenticated" views.

Thanks!
```
Model: `Claude Opus 4.6 (Thinking)`
PromptID: `8`

[SESSION Continues (After 7min. I ran out of quota)]
```
Please continue where you left-off with the previous prompt(""
I added 'order_index' to schema_columns-table (to order columns on schema.table-view).

You are an expert Laravel/PHP developer
Please go into views: schema.column and schema.table and remove the CSS.
I want to create the entire CSS myself, so please keep (and maybe add) some classes.
Then install blade-icons via "composer require blade-ui-kit/blade-icons" and "php artisan vendor:publish --tag=blade-icons" and  implement the "ordering"-feature on schema.table view (also update the icons on the views ands IN NO CASE use emojis).
After that is finished add breadcrombs to all other "authenticated" views.

Thanks!
"")

you may look/review what you already did.
```
Model: `Gemini 3.1 Pro (High)`
PromptID: `9`
<hr>


### 2026-07-16

[NEW SESSION]
```
I have a Laravel Codebase here (project name: SchemaBuilder).

You are a Senior Laravel and PHP expert. Your Task is to review the entire codebase, make sure all functionality is actually functional and add missing functionality. You are also tasked with creating some tests to ensure functionality.

you may only stop after you ensured everythging is fully functional.

If you encounter any major issues, bugs or logical errors: request Review from teh User(me).

output your findings to: llm_output/{date}_{time}_{slug}.md and perform a git-commit fully by yourself
```
Model: `Claude Sonnet 4.6 (Thinking)`
PromptID: `10`


[SESSION Continues]
```
I made some little changes.

please review app.css and add some minimalistic new styles based on teh current styles and the classes already present on teh blade-views.

ALso: add a very minimalistic "home"-view content (pages.home view).

Thanks!
```
Model: `Claude Sonnet 4.6 (Thinking)`
PromptID: `11`

_**note:** PromptID is for reference from ROADMAP.md_