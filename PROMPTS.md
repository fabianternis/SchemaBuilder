# A detailed List of Prompts that i gave to my AI-Agent

### 2026-07-11

[NEW SESSION]
#### `please update SCHEMA.md by adding the 'sessions'- and 'password_reset_tokens'-table from teh very first migration. then: update tzhe migration and create a new migration(and model) for Project(s)`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `1`
<hr>

[NEW SESSION]
#### `Review routes/web.php. Based on thr routing, i prepared, create all the Controllers neccessary and some very simple views. Also create the schema-routing.`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `3`
<hr>

[SESSION Continues]
#### `I did not provide enough context relating teh schema. I wanted all shcema-related models to be handled within the smae routing-group and Controller (the routes may even be e.g. "/{project_slug}/{database_name}/{table_name}/{column_name}") ... please rework that and update the Controller, routing and the schema-views.`
Model: `Gemini 3.5 Pro (High reasoning`
PromptID: `4`
<hr>

[NEW SESSION]
#### `Please review the Project-model (I added $schema which i used to auto-genrate resource-views and validfation logic.). Please review the first two functions (create() and index()) of the ProjectController and the resources.index as well as resources.create views. Then finish the ProjectController and create the resources.show as well as resources.edit views (ALL BASED on the $schema inside the Project-model). If you have any fdeedback regarding the $schema please create a file as llm_output/{date+time}_{slug}.md.`
Model: `Gemini 3.5 Pro (High reasoning)`
PromptID: `5`
<hr>

[SESSION Continues]
#### `Please perofmr your feedback (except 5.) which i will implement something special for, myself (also removbe "preferences" from the $schema on Project). Then: Update the stzore() (and maybe updat()), regarding the morph "owber" (owner_type seems not to get set on db-quirey) (""[Error LOG (190+ Lines)]"")`
Model: `Gemini 3.5 Pro (High reasoning`
PromptID: `6`
<hr>



_**note:** PromptID is for reference from ROADMAP.md_