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
- owner_id(uuid/ulid)
- owner_type(string?)
- name
- [slug(unique)]
- (description)
- (production_url) (maybe in preferences)
- (repo_url) (maybe in preferences)
- prefereces(json)
- timestamps
- [deleted_at]

_**note:** This is not the compolete Schema but a initial thaught_


## Legend
### What means what
XYZ() more details
(XYZ) Optional
[XYZ] maybe(= not sure if it'll be there)