# Carry-forward roadmap

## Completed foundations

- UserAdminController pipeline locale persistence.
- Named argument locale hotfix.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Do not mix positional arguments after named arguments in generated PHP code.
- Do not blindly write `$_POST` or arbitrary `setData()` values to core tables.
- Every persisted field must be declared through a field definition/write map.
- Generated SQL must be based on field-definition approved core write data only.
- Admin locale values must be normalised and strictly validated before persistence.
- Empty admin locale values should persist as null to preserve configured admin-locale fallback.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.

## Future TODOs

- Phase 1.18: Entity extension data persistence table for third-party fields.
- Phase 1.19: before/after validate/save event dispatcher integration.
- Replace hard-coded en_AU locale helper with SupportedLocaleProvider injection if/when UserAdminController receives services cleanly.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add server-side block renderer integration.
- Add safe content_format=block_json switch.
- Add media library with uploads stored outside public first.
- Add pagination to admin grids.
- Add customer login and customer account management.
- Add admin menu link to mail logs
- add form_key to forms to avoid stale form submissions and hack prevention
- Don't use raw queries as it will break true modern modularity of the CMS. Also using Data Models/Objects will help developers to use different SQL clients. (MySQL, MariaDB, MSSQL, PostgreSQL etc).