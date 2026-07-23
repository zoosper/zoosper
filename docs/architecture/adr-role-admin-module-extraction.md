# ADR: Role Admin Module Extraction

## Status

Proposed / deferred until after configuration layering and extension seams.

## Context

After Phase 1.38, `RoleAdminController` is clean enough to be considered a candidate for future module/package extraction. A possible package name is:

```text
zoosper-admin-roles
```

The role-admin area contains routes, controller logic, view partials, permission tree rendering, user assignment rendering, repository usage, CSRF handling, admin layout integration, and audit logging.

## Decision

Do not extract role-admin into a separate package immediately.

Keep it in `zoosper-admin` for now, but maintain it as an extraction-ready vertical slice with clear boundaries:

- controller is request orchestration only;
- view partials own HTML;
- repository contracts remain explicit;
- ACL/menu/route ownership should be documented;
- future assets for permission-tree UX should be module-owned.

## Rationale

Immediate extraction would be premature because role-admin still depends on several core/admin services that are not yet fully package-boundary friendly:

- config layering is not complete;
- module asset ownership needs a stable convention;
- package-owned admin menu/ACL defaults need clearer override semantics;
- extension seams for repository/service behaviour are still evolving;
- tests and migration ownership conventions need to be simple for third-party package authors.

## Consequences

### Positive

- Avoids premature package split.
- Keeps 1.39 focused on platform fundamentals.
- Preserves clean extraction path without forcing it now.

### Negative

- Role-admin code remains inside `zoosper-admin` for the short term.
- Future package extraction will still require deliberate route/menu/ACL/service migration work.

## Revisit criteria

Revisit extraction after:

1. configuration layering is complete;
2. module assets have stable conventions;
3. package-owned routes/menu/ACL are documented and tested;
4. service override/extension semantics are stable;
5. permission-tree UI has been upgraded behind module-owned assets.
