# RoleAdminController Cutover Context Export

This document defines the source-context export required before the final Phase 1.38 controller patch.

## Why this phase exists

The RoleAdminController to Latte migration is source-specific. The final patch must match the exact current project conventions for:

- controller constructor dependencies;
- response types;
- render/view helpers;
- role route/action method names;
- role admin templates;
- controller config/service wiring;
- CSRF token naming and propagation;
- current inline HTML/heredoc structure.

This phase exports that context locally into `var/reports` so the final implementation phase can be authored from real source rather than assumptions.

## Tool

```text
tools/export-role-admin-cutover-context.php
```

## Outputs

The tool writes:

```text
var/reports/role-admin-cutover-context/
var/reports/role-admin-cutover-context-manifest.txt
var/reports/role-admin-cutover-context.log
```

If PHP `ZipArchive` is available, it also writes:

```text
var/reports/role-admin-cutover-context.zip
```

## What gets exported

The export attempts to include:

- `RoleAdminController.php`;
- role admin Latte templates;
- role/admin related config files where discoverable;
- render/view source signal files where discoverable;
- a manifest with file paths, public methods, constructor parameters, and inline markup signals.

## Commit hygiene

Generated `var/reports` files are local evidence and should not normally be committed.

## Next phase

Use the generated context export to build the exact controller cutover patch. The next phase should stop creating planning-only infrastructure and should either:

1. apply a source-specific controller patch; or
2. add a named safe pattern to the guarded cutover harness and run it.
