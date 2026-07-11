# The Entire Project Roadmap

## 1 Schema
### 1.1 I created SCHEMA.md wit hteh initial tables `users` and `projects`
### 1.2 I prmpted Gemini (ref. PromptID:`1` in PROMPTS.md) to update the SCHEMA.md based on pre-existing migrations that ship with the framework and update the migrations and models of this Project based on my SCHEMA.
### 1.3 I refined SCHEMA.md by adding all teh schema-related Tabled (table-prefix: `schema_`)
### 1.4 I promted Gemini (with a new Session, ref. PromptID: `2`in PROMPTS.md) to update the Project's migrations and Models based on my updated SCHEMA.md as well as removing all NodeJS-related things from teh Project (due to my unability to debug that S**it)

## 2 basic Auth and other Stuff
### 2.1
#### 2.1.1 I started creating some blade-views(including layout and components)
#### 2.1.2 I created the `PageController` and some Routes
#### 2.1.3 I created the `auth.*`(`auth.login`+`auth.signup`) Views and the `AuthController`.
#### 2.1.4 After finishing Auth, I created a one-line message on `pages.dashboard` View
#### 2.1.5 I did some other, small things
#### 2.1.6 I prepared the Routing, how i want it to be
#### 2.1.7 I Prompted gemini to create some simple Controllers and minimalist views based on my routing. Due to me not providing enough context (PromptID: `3`), gemini hallucinated something Schema-related.
#### 2.1.8 After I snet another Prompt within the same session, the LLM still messed up, so i decided to also do this simple stuff myself.