# Dead Views — Cleanup Findings (26 Jul 2026)

The unused-file scans surfaced the following. LIVE = referenced by a controller;
DEAD = referenced only by tests or nothing.

## Roles views — the important one

- LIVE: `app/zoosper-admin/resources/views/admin/roles/*.php`
  (loaded by `RoleAdminController::renderRoleView()` — `dirname(__DIR__,2)/resources/views/admin/roles/`).
  ACTION: the CSRF fix (`name="_csrf_token"`) MUST be applied to `form.php` HERE.
- DEAD: `app/zoosper-core/views/admin/roles/{index,form}.latte`
  (scaffolded for a Latte cutover that never wired; referenced only by
  RoleAdminLatteViewScaffold/CutoverPlan tests). Editing CSRF here has no runtime effect.
- DEAD: `app/zoosper-auth/resources/views/admin/roles/{index,form}.php`
  (superseded 2026-07-10 originals).

## Other unreferenced view files (deletion candidates)

- `app/zoosper-auth/resources/views/admin/users/{index,form,message}.latte`
- `app/zoosper-page/resources/views/admin/page-momentum.latte`
- `app/zoosper-page/resources/views/page/view.latte`
- `app/zoosper-page/resources/views/page/view.php`

Confirm each against its controller before deleting. Several `assertFileExists`
tests PIN these dead files — delete those tests in the same commit (they assert
process artifacts exist, not behaviour; both reviews flagged this pattern).

## Recommended durable approach

Add a real dead-code check to CI instead of hand-hunting:

- `composer require --dev icanhazstring/composer-unused` (unused Composer deps)
- Install Psalm and run `vendor/bin/psalm --find-unused-code` (unreferenced
  classes/methods) — this would auto-flag the dead 2FA `Service/*` tree and the
  duplicate `Zoosper\Admin\PageMomentum\*` stack.

Note: `vimeo/psalm` must be installed via Composer first:
`composer require --dev vimeo/psalm` then `vendor/bin/psalm --init`.
