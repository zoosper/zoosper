# RoleAdminController Cutover Pattern Discovery

This document records the final discovery step before encoding the source-specific RoleAdminController Latte cutover.

## Why this phase exists

The guarded cutover executor reported:

```text
SAFE_PATTERN none
```

That means the controller still has inline markup, but the executor does not yet know the exact source shape well enough to mutate it safely.

## Goal

Export the exact method bodies that own role-admin markup so the next phase can either:

1. add a named safe pattern to `tools/apply-role-admin-latte-cutover.php`; or
2. ship a hand-authored source-specific patch for `RoleAdminController.php`.

## Tool

```text
tools/discover-role-admin-cutover-pattern.php
```

## Output

The tool writes:

```text
var/reports/role-admin-cutover-pattern.txt
var/reports/role-admin-cutover-pattern.log
var/reports/role-admin-cutover-pattern-source/
```

The source directory contains copies/excerpts for:

- `RoleAdminController.php`;
- `AdminLayout.php`;
- `index()`;
- `createForm()`;
- `editForm()`;
- constructor parameter list;
- detected inline markup signals.

## Next phase rule

The next phase should stop adding discovery-only tools and should use this exported method context to implement the actual cutover.
