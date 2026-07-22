# Guarded RoleAdminController Cutover

This document defines the safety contract for the source-specific RoleAdminController to Latte cutover.

## Why this guard exists

The actual controller rewrite must match the current codebase exactly. The cutover depends on:

- the current `RoleAdminController` constructor;
- the current response and rendering conventions;
- existing route method names;
- CSRF token handling;
- role repository/service dependencies;
- the existing inline HTML structure.

A generic search-and-replace patch would be risky. The guarded cutover harness therefore runs in read-only mode by default and refuses to apply source changes unless it recognises a known safe source pattern.

## Tool

```text
tools/guard-role-admin-controller-cutover.php
```

Default mode:

```bash
php8.5 tools/guard-role-admin-controller-cutover.php
```

Apply mode:

```bash
php8.5 tools/guard-role-admin-controller-cutover.php --apply
```

## Safety rules

The harness must:

1. locate `RoleAdminController.php`;
2. confirm the role admin Latte templates exist;
3. detect current inline HTML/heredoc signals;
4. detect render/view source signals;
5. write a report before any apply attempt;
6. refuse `--apply` unless a known safe pattern is detected;
7. never alter route paths, ACL names, CSRF middleware behaviour, database schema, or repository semantics.

## Expected report files

```text
var/reports/role-admin-guarded-cutover.txt
var/reports/role-admin-guarded-cutover.log
```

Generated `var/reports` files should not normally be committed.

## Next implementation rule

If the guarded harness refuses `--apply`, the generated report should be used to produce an exact source-specific controller patch rather than forcing a generic rewrite.
