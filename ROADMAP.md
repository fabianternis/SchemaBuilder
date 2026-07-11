# The Entire Project Roadmap

## 1 Schema
### 1.1 I created SCHEMA.md wit hteh initial tables `users` and `projects`
### 1.2 I prmpted Gemini (ref. PromptID:`1` in PROMPTS.md) to update the SCHEMA.md based on pre-existing migrations that ship with the framework and update the migrations and models of this Project based on my SCHEMA.
### 1.3 I refined SCHEMA.md by adding all teh schema-related Tabled (table-prefix: `schema_`)
### 1.4 I promted Gemini (with a new Session, ref. PromptID: `2`in PROMPTS.md) to update the Project's migrations and Models based on my updated SCHEMA.md as well as removing all NodeJS-related things from teh Project (due to my unability to debug that S**it)