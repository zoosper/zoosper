# Carry-forward roadmap

## Completed foundations

- Entity Extension Data Persistence Table foundation.
- Error notice selector alias hotfix.
- Entity Save Lifecycle Events foundation.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Use correct modern programming terminology and include plain-English explanation for planning-heavy phases.
- Save flows should dispatch before/after validation and before/after save lifecycle events.
- Validation listeners may add errors and block persistence.
- Save-before listeners may mutate data before persistence.
- Save-after and commit-after listeners should react to successful persistence and avoid changing the already-written core payload.
- Third-party module fields must persist through extension storage or module handlers unless explicitly mapped as core columns.
- Every persisted field must be declared through a field definition/write map.
- Every SQL placeholder token must have a matching execute/bind parameter.
- Admin notices must use canonical selectors such as `.notice-success`, `.notice-error` and future `.notice-warning`.

## Next roadmap phases

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
