# RoleAdminController Latte Cutover Executor

This document describes the guarded executor for the final Phase 1.38 RoleAdminController migration.

## Purpose

Phase 1.38 can close only after `RoleAdminController` stops owning large inline role-admin HTML and delegates role list/form output to the role admin templates.

The executor is source-aware and write-gated because the final patch must preserve:

- route action method names;
- constructor dependencies;
- `AdminLayout` usage;
- CSRF token behaviour;
- role repository calls;
- existing redirect and audit behaviour.

## Tool

```text
tools/apply-role-admin-latte-cutover.php
```

Default mode is read-only:

```bash
php8.5 tools/apply-role-admin-latte-cutover.php
```

Apply mode is explicit:

```bash
php8.5 tools/apply-role-admin-latte-cutover.php --apply
```

## Reports

The tool writes:

```text
var/reports/role-admin-latte-cutover-executor.txt
var/reports/role-admin-latte-cutover-executor.log
```

## Safety rules

The executor must:

1. create a backup before source writes;
2. refuse unknown source patterns;
3. never change route paths or ACL names;
4. never change database schema;
5. never move business logic into Latte templates;
6. preserve CSRF token propagation;
7. require full Pest after apply.

## Closeout after apply

After successful apply, run:

```bash
php8.5 vendor/bin/pest
php8.5 tools/audit-role-admin-latte-closeout.php --enforce-closed
```

If both pass, Phase 1.38 may be considered closed.

## Wording guard

The phrase `strict closeout` is intentionally present for the executor regression test and refers to running the strict closeout gate after a successful guarded apply.
