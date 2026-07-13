# Carry-forward roadmap

## Completed foundations

- Entity Extension Data Persistence Table foundation.
- Roadmap and error CSS verification hotfix.
- Error notice selector alias hotfix.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Admin notices must use canonical selectors such as `.notice-success`, `.notice-error` and future `.notice-warning`.
- Admin notices must retain visible success/error/warning styling after UI changes.
- Verifiers should avoid being too brittle around equivalent wording, but selectors used by rendered markup should be canonical.
- Every SQL placeholder token must have a matching execute/bind parameter.
- Every persisted field must be declared through a field definition/write map.
- Third-party module fields must persist through extension storage or module handlers unless explicitly mapped as core columns.
- Use correct modern programming terminology, even when planning from informal wording.
- When pausing for planning, include pros, cons, risks, implementation options and roadmap updates.
- Do not blindly write `$_POST` or arbitrary `setData()` values to core tables.
- Every persisted field must be declared through a field definition/write map.
- Generated SQL must be based on field-definition approved core write data only.
- Every SQL placeholder token must have a matching execute/bind parameter.
- Verifiers must check placeholder/parameter consistency after SQL write-map patches.
- Third-party module fields must stay available in the save data object, but persist through extension storage or module handlers unless explicitly mapped as core columns.
- Extension-table fields must be stored outside core entity tables by entity type, entity id, module and field name.
- Handler fields such as passwords and role assignments must be processed by dedicated handlers, not automatic core column writes.
- Admin notices must retain visible success/error/warning styling after UI changes.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- 
## Next roadmap phases

### Phase 1.20 - Entity Save Lifecycle Events

- Dispatch before/after collect, validate, save and commit events.
- Allow modules to validate and mutate data before save.
- Allow modules to react after save without touching core controllers.

### Phase 1.21 - AdminUser extension data integration

- Wire `EntityExtensionDataPersister` into an AdminUser save event or handler.
- Verify a sample third-party AdminUser extension field persists.

### Phase 1.22 - AdminUser save flow full pipeline migration hardening

- Move more AdminUser create/update behaviour through save pipeline services.
- Keep password and role assignment as dedicated handlers.
- Add browser-oriented verification and regression checks.

### Future TODOs

- Add pagination to admin grids.
- Add customer login and customer account management.
- Add admin menu link to mail logs.
- Add form key / CSRF protection to forms.
- Replace hard-coded `en_AU` locale helper with `SupportedLocaleProvider` injection when controller dependencies are clean.
- Add per-site locale settings from `SiteContext` / `SiteRepository`.
- Add server-side block renderer integration.
- Add safe `content_format=block_json` switch.
- Add media library with uploads stored outside public first.
- Add warning notice CSS and verifier.
- Add developer documentation for field definitions, extension data and lifecycle events.
