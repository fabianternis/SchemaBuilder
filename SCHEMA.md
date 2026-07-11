# Schema
## The actual Schema of the SchemaBuilder xD

### User (`users`)
- id(uuid/ulid)
- username
- email
- email_verified_at(timestamp)
- (password)
- (github_id)
- (github_token)
- (github_refresh_token)
- timestamps
- [deleted_at] (Maybe later)

### Project (`projects`)
- id(uuid/ulid)
- owner_id(uuid/ulid) (MORPHS)
- owner_type(string?) (MORPHS)
- name
- [slug(unique)]
- (description)
- (production_url) (maybe in preferences)
- (repo_url) (maybe in preferences)
- prefereces(json)
- timestamps
- [deleted_at]


### Password Reset Tokens (`password_reset_tokens`)
- email(primary)
- token
- created_at(timestamp)

### Sessions (`sessions`)
- id(primary)
- user_id(foreign)
- ip_address
- user_agent
- payload
- last_activity(index)

### Database (`schema_databases`)
_**note:** A Project can have optionally multiple databases but one is auto-generated on Project-creation._
- id(uuid/ulid) (AI decided: ulid)
- project_id(uuid/ulid)
- name
- (displayname)
- ~~[(slug)]~~
- (description)
- timestamps
- [(deleted_at)]

### Table (<!--`db_tables`-->`schema_tables`)
- id(uuid/ulid)
- database_id(uuid/ulid)
- name

### Column (`schema_columns`)
_**thaughts:** maybe a arributes(json) would be better_
- id(uuid/ulid)
- table_id(uuid/ulid)
- name
- type(enum)
- is_nullable
- is_primary
- (default)
- is_unique
- on_cascade
- (length)
- auto_increment
- referenced_table_id
- [(deleted_at)]
- timestamps



_**note:** This is not the complete Schema but a initial thaught_


## Legend
### What means what
XYZ() more details
(XYZ) Optional
[XYZ] maybe(= not sure if it'll be there)