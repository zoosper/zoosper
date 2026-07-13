# Carry-forward roadmap

## Completed foundations

- Admin user locale UI rendering.
- Admin user locale persistence through repository create/update paths.
- Named argument locale hotfix.
- PDO locale parameter hotfix.
- Admin success notice CSS restoration.
- Admin error notice CSS restoration.
- Admin entity save pipeline foundation.
- AdminUser field definition provider and write map.
- AdminUser save data pipeline.
- AdminUser core write migration support.
- UserAdminController save-flow discovery.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Use correct modern programming terminology, even when planning from informal wording.
- When pausing for planning, include pros, cons, risks, implementation options and roadmap updates.
- Do not blindly write `$_POST` or arbitrary `setData()` values to core tables.
- Every persisted field must be declared through a field definition/write map.
- Generated SQL must be based on field-definition approved core write data only.
- Every SQL placeholder token must have a matching execute/bind parameter.
- Verifiers must check placeholder/parameter consistency after SQL write-map patches.
- Do not mix positional arguments after named arguments in generated PHP code.
- Admin notices must retain visible success/error/warning styling after UI changes.
- Third-party module fields must stay available in the save data object, but persist through extension storage or module handlers unless explicitly mapped as core columns.
- Handler fields such as passwords and role assignments must be processed by dedicated handlers, not automatic core column writes.
- Save flows should dispatch before/after validation and before/after save lifecycle events.
- Admin locale values must be normalised and strictly validated before persistence.
- Empty admin locale values should persist as null to preserve configured admin-locale fallback.
- Prefer generic entity save pipelines over brittle controller-specific patches.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Keep controllers thin and move business logic into services, repositories, handlers or pipelines.
- Avoid raw SQL across controllers; repositories/query services should own persistence.
- Design persistence abstractions so future database engines such as MySQL, MariaDB, PostgreSQL, Microsoft SQL Server or SQLite can be supported where practical.

## Restored TODOs

- Add pagination to admin grids.
- Add customer login and customer account management.
- Add admin menu link to mail logs.
- Add form key / CSRF protection to forms to avoid stale submissions and reduce tampering risk.
- Continue reducing raw query usage where it blocks true modularity and database portability.

## Next roadmap phases

### Phase 1.18 - Planning and roadmap consolidation

- Document completed work.
- Restore accidentally removed roadmap TODOs.
- Document pros/cons for extension data persistence.
- Prepare the next implementation phase.

### Phase 1.19 - Entity Extension Data Persistence Table

- Add `entity_extension_values` migration.
- Add repository for extension values.
- Add persister that saves `FieldStorageType::ExtensionTable` fields.
- Verify third-party fields persist outside core tables.

### Phase 1.20 - Entity Save Lifecycle Events

- Dispatch before/after collect, validate, save and commit events.
- Allow modules to validate and mutate data before save.
- Allow modules to react after save without touching core controllers.

### Phase 1.21 - AdminUser save flow full pipeline migration hardening

- Move more AdminUser create/update behaviour through save pipeline services.
- Keep password and role assignment as dedicated handlers.
- Add browser-oriented verification and regression checks.

### Future TODOs

- Replace hard-coded `en_AU` locale helper with `SupportedLocaleProvider` injection when controller dependencies are clean.
- Add per-site locale settings from `SiteContext` / `SiteRepository`.
- Add server-side block renderer integration.
- Add safe `content_format=block_json` switch.
- Add media library with uploads stored outside public first.
- Add warning notice CSS and verifier.
- Add developer documentation for field definitions, extension data and lifecycle events.
