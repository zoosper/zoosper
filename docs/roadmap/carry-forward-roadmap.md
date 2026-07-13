# Carry-forward roadmap

## Completed foundations

- Post-save admin user locale persistence hotfix attempt.
- Admin entity save pipeline foundation.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Do not blindly write `$_POST` or arbitrary `setData()` values to core tables.
- Every persisted field must be declared through a field definition/write map.
- Third-party module fields must stay available in the save data object, but persist through extension storage or module handlers unless explicitly mapped as core columns.
- Save flows should dispatch before/after validation and before/after save lifecycle events.
- Admin locale values must be normalised and strictly validated before persistence.
- Empty admin locale values should persist as null to preserve configured admin-locale fallback.
- Prefer generic entity save pipelines over brittle controller-specific patches.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.

## Future TODOs

- Phase 1.13: AdminUser field definition provider and write map.
- Phase 1.14: Migrate UserAdminController save flow to EntityDataObject and FieldDefinitionRegistry.
- Phase 1.15: Entity extension data persistence table for third-party fields.
- Phase 1.16: before/after validate/save event dispatcher integration.
- Replace hard-coded en_AU locale helper with SupportedLocaleProvider injection if/when UserAdminController receives services cleanly.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
