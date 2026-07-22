# RoleAdminController Cutover Patch Generation

This document defines the local candidate patch-generation step for the Phase 1.38 role admin Latte migration.

## Purpose

The previous phases captured source context, added target Latte templates, and introduced a guarded cutover harness. This phase adds a local generator that can inspect the current repository and produce a candidate patch/report without changing source automatically.

## Tool

```text
tools/generate-role-admin-cutover-patch.php
```

Default command:

```bash
php8.5 tools/generate-role-admin-cutover-patch.php
```

## Outputs

The tool writes runtime artefacts only:

```text
var/reports/role-admin-cutover-candidate.patch
var/reports/role-admin-cutover-generation.txt
var/reports/role-admin-cutover-generation.log
```

These files are generated evidence and should not normally be committed.

## Behaviour

The generator:

1. locates `RoleAdminController.php`;
2. verifies role admin Latte templates exist;
3. inspects controller public methods and constructor parameters;
4. records inline HTML/heredoc signals;
5. records render/view source signals;
6. writes a candidate patch file explaining the safest next source-specific change.

## Safety

The generator does not modify `RoleAdminController.php`.

If it cannot infer a safe exact patch, the generated `.patch` file contains a guarded manual patch brief rather than a fake diff. This is intentional. The actual controller cutover must match the repository's current renderer convention precisely.

## Next phase

After reviewing the generated candidate patch/report, the next phase can either:

- add the source-specific safe pattern to the guarded cutover harness; or
- apply a hand-authored controller patch with tests proving the controller no longer owns large inline role-admin markup.

## Wording guard

The generator is intentionally non-mutating: it writes candidate patch and report artefacts under `var/reports/` only and does not modify `RoleAdminController.php`.
