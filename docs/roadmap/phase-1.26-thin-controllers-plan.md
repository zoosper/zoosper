# Phase 1.26 Plan - Thin Controllers / View Layer Extraction

## Goal

Remove embedded HTML from `PageAdminController` and `UserAdminController` (siblings
later), moving it into Latte templates. Leave the controllers as request handlers:
permission -> CSRF -> read request -> call service -> render template.

## Why now

- **User-identified architectural debt** - the controllers are oversized because
  they mix request handling, HTML, and some logic.
- Aligns with **strong foundation before features**: thinning the controllers
  de-risks *every* future admin feature.
- **Low feature-risk, high maintainability payoff** - behaviour is preserved, and
  we have Pest tests + a manual smoke checklist to catch drift.

## Options considered

| Option | Pros | Cons |
|---|---|---|
| **View-layer extraction now** (recommended) | Fixes the debt the user flagged; makes future admin work safe and small; enables theme/template overrides | Touches large controller files (mitigated: incremental, one screen at a time) |
| Module listener discovery first | Improves modularity of listeners | Doesn't address the oversized-controller smell the user raised |
| Schema-engine unification first | Big architectural win | Larger blast radius; better done after controllers are clean |

**Decision: view-layer extraction now.**

## Scope IN

- **PageAdminController**: extract the index list, create/edit forms, status
  buttons, and notices into Latte templates + partials.
- **UserAdminController**: extract the listing, create/edit form, role checkboxes,
  locale field, 2FA panel, and notices into Latte templates + partials.
- Confirm/introduce Latte templates under each module's views/templates path.
- Add thin **view-model builders** where they keep the controller clean.
- **Keep identical**: method signatures, routes, and status codes
  (419 / 422 / 404 / 200 / redirects).

## Scope OUT (guardrails)

- No route changes.
- No new features.
- No schema or data changes.
- Do **not** touch `RoleAdminController` this phase.
- Do **not** change entity-save lifecycle behaviour.

## Risks & mitigations

| Risk | Mitigation |
|---|---|
| Template path / namespace convention unknown | Resolved by the included `bin/dump-view-layer.php` **before** any code |
| Rendered HTML drifts from current output | Compare output for parity; keep the same markup/classes |
| Large blast radius | Do **PageAdminController first**, verify, then UserAdminController |
| No HTTP test harness yet | Add a lightweight render/Feature smoke where feasible + a manual checklist |

## Acceptance criteria

- Controllers contain **no** `<<<HTML` heredoc blocks for these screens.
- All screen HTML lives in `.latte` templates.
- Pest suite still green.
- Manual smoke checklist passes: list; create invalid -> 422 with message; create
  valid -> success; edit; publish/unpublish; admin-user create/edit; 2FA reset.

## Dependencies I need (paste inline, or via the locator dump)

To design the extraction with zero guessing, I need:

- `app/zoosper-admin/src/UI/AdminViewRenderer.php`
- `app/zoosper-admin/src/Layout/AdminLayout.php`
- the `theme.admin_template_renderer` service/class (the Latte renderer)
- `app/zoosper-admin/src/Form/AdminFormRenderer.php`
- one existing example `.latte` template
- the templates/views directory layout
- how a module declares its template namespace (e.g. `zoosper-page::`)

`bin/dump-view-layer.php` collects these automatically.

## Sequenced steps

1. **Locate** the view/template system (run the dump).
2. Extract `PageAdminController::index` -> template.
3. Extract page create/edit form -> template(s) + partials.
4. **Verify** (Pest + smoke).
5. Repeat for `UserAdminController`.
6. Remove the now-dead heredoc blocks + helper methods.
7. Update docs.
8. Retire any matching `verify-*` scripts into Pest.
