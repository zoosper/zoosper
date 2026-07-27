# RoleAdmin ACL Groups via ConfigRepository

## Problem

`RoleAdminController::permissionTree()` loaded ACL group definitions via a raw
runtime `require` of exactly one file
(`zoosper-auth/config/acl.php`), bypassing `ConfigRepository`/
`ModuleConfigAggregator` entirely. Every other config access in the codebase
goes through `ConfigRepository`, which merges `config/acl.php` across ALL
modules — so this controller silently could not see project-level or
other-module ACL group overrides.

## Fix

`aclGroups()` prefers an injected `ConfigRepository` (`$config->array('acl')`)
and falls back to the original single-file `require` when no `ConfigRepository`
is provided. The new constructor parameter is optional and last, so this ships
without any DI wiring change; the layering benefit only takes effect once
`ConfigRepository::class` is added to the controller's factory.

## Status

Code change shipped (Phase 1.111). DI wiring + a real automated test require
`ConfigRepository.php` (and ideally the controller's factory file), which were
not available at the time of this phase.
