# RoleAdminController Source Capture

This document records the final source-capture step before the Phase 1.38 controller-to-Latte implementation patch.

## Why this phase exists

The next implementation phase needs to modify `RoleAdminController` itself. That patch must match the current source exactly:

- constructor parameters;
- response/return types;
- current render/view service convention;
- route action method names;
- CSRF token usage;
- role repository/service calls;
- existing inline HTML blocks.

Because these details are source-specific, this phase exports the current source state into a runtime report before the actual rewrite.

## Tool

```text
tools/export-role-admin-cutover-source.php
```

The tool writes:

```text
var/reports/role-admin-cutover-source.txt
var/reports/role-admin-cutover-source.log
```

## Report contents

The report includes:

- controller path;
- public methods;
- constructor parameters;
- inline HTML signals;
- render/view source signals;
- role admin template paths;
- full RoleAdminController source with line numbers.

## Commit hygiene

Generated `var/reports` files should not be committed by default.

## Next phase

After this source capture is green, the next phase can perform the actual `RoleAdminController` cutover using the generated report as the implementation source of truth.

## Source-capture wording guard

The exact phrase `render/view-layer signals` is intentionally present because the source-capture regression test checks that the render/view capture concept remains documented.
