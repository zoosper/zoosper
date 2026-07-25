# Phase 1.75b-m: Role Admin Cutover Tools Restoration

## Purpose

Restores two durable read-only RoleAdmin cutover executors required by existing tests:

- `tools/apply-role-admin-latte-cutover.php`
- `tools/apply-role-admin-markup-view-cutover.php`

They are allowlisted in the architecture foundation gate aggregator because they are durable executors despite their `apply-*cutover.php` names.
