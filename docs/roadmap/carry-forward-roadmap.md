# Carry-forward roadmap

## Completed foundations

- Named argument locale hotfix.
- PDO locale parameter hotfix.
- Admin error notice CSS restoration.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Always include meaningful PHPDoc and helpful comments.
- Every SQL placeholder token must have a matching execute/bind parameter.
- Verifiers must check placeholder/parameter consistency after SQL write-map patches.
- Admin notices must retain visible success/error/warning styling after UI changes.
- Do not mix positional arguments after named arguments in generated PHP code.
- Do not blindly write `$_POST` or arbitrary `setData()` values to core tables.
- Every persisted field must be declared through a field definition/write map.
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