# Phase 1.17 - UserAdminController Pipeline Locale Persistence

The save-flow inspection showed that `UserAdminController` uses `create()` and `update()` methods while `AdminUserRepository` exposes `createWithRoleIds()` and `updateUser()`. The repository already hydrates `locale`, but its create/update write paths did not persist locale.

This phase patches the concrete save path:

- `AdminUserRepository::createWithRoleIds()` accepts and writes `?string $locale`.
- `AdminUserRepository::updateUser()` accepts and writes `?string $locale`.
- `UserAdminController` normalises locale through `AdminUserSaveDataFactory`.
- Controller calls pass the normalised locale into repository create/update methods.

Password and role assignment remain handler flows, not automatic core-column writes.
