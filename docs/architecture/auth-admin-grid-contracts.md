# Auth admin Grid contracts

Phase 4L introduces stable shared-Grid definitions for Admin Users and Roles while
leaving the existing CRUD, CSRF, permissions, password handling and transactional
role assignment paths unchanged.

## Ownership

- `zoosper/auth` owns users, roles, permissions, repositories and feature Grid definitions.
- `zoosper/grid` owns generic definitions, columns, filters and rendering primitives.
- `zoosper/admin-grid` owns per-admin workspace state, bookmarks and interactions.
- `zoosper/admin` owns the common admin shell and module-asset delivery.

## Security boundary

The Admin Users definition deliberately excludes password hashes, two-factor data,
recovery codes and secrets. The Roles definition exposes no permission payload in
listing rows. Existing `user.manage` and `role.manage` route boundaries remain
unchanged.

## Marko direction

Do not split users and roles into additional Zoosper packages during this cutover.
The existing `zoosper/auth` module is already the cohesive domain boundary. A later
compatibility review can compare its schema and security contracts with
`marko/admin-auth` without coupling the Grid migration to an authentication rewrite.

## Next cutover

The next phase can add allow-listed paginated read models and switch the two live
index actions to the compact workspace. Create/edit and role-assignment writes stay
on their existing hardened paths.
