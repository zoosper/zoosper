## Zoosper Agent Guidelines

Zoosper is a PHP 8.5+ CMS built with Marko-inspired modular architecture,
now with real, verified Marko package adoption where it genuinely fits
(see ROADMAP.md §14).

### Project rules

- Keep controllers thin.
- Put business logic in services.
- Put persistence in repositories.
- Use strict types in every PHP file.
- Prefer constructor injection.
- Do not use service locator style inside business logic.
- Escape frontend/admin output by default.
- Use parameterised SQL only.
- Keep modules isolated and replaceable.
- Do not change public contracts casually.
- Add or update docs when adding a feature.
- Write readable code; do not compress or golf code.
- **Before building any new subsystem from scratch, check Marko's real
  package catalog first** — by reading actual installed source or real
  docs, never by assuming from a package name alone. Only adopt a Marko
  package after verifying its real API against the codebase's actual
  needs (see ROADMAP.md §14 for worked examples of this discipline,
  including one case — `marko/database` — where the right call was to
  defer adoption after verification, not force a fit).
- Prefer lighter-weight, timeless doc-comments in source code over long
  narrative "fixed on <date>, confirmed by <reviewer>" essays — that
  history belongs in commit messages and ROADMAP.md's daily log, not
  permanently embedded in source, where it goes stale after the next
  refactor.
- When a module is extracted into its own standalone package under
  `packages/`, add a `.gitattributes` marking `tests/` and dev-only
  tooling as `export-ignore` (matching Marko's own package convention) —
  not needed for `app/*` modules, which are path-repository entries, not
  separately-exported packages.

### Current modules

**`app/` — path-repository modules (monorepo-internal, not yet
independently exportable):**
- `zoosper-core`: bootstrap, config, database, routing, HTTP, security
  utilities, the module-manifest compile step, and the cache-driver
  factory/adapter (file or Redis, via Marko).
- `zoosper-auth`: admin users, roles, permissions, login/session services,
  CSRF + auth middleware, rate limiting.
- `zoosper-admin`: admin HTTP controllers, audit log, admin grid/form
  foundations.
- `zoosper-api`: API controllers and JSON response shape.
- `zoosper-site`: site/domain resolution.
- `zoosper-page`: page entity, repository, rendering and frontend routing
  (including the opt-in page-cache wiring).
- `zoosper-theme`: theme resolution, template engines, layout updates.
- `zoosper-two-factor`: TOTP enrolment/challenge/reset, recovery codes,
  encryption-key rotation support.
- `zoosper-media`: media library, uploads, Editor.js image integration.
- `zoosper-mail`: SMTP mailer, logged mailer, email diagnostics.
- `zoosper-url-rewrite`: URL rewrite management.
- `zoosper-install`: installer/scaffolding commands.

**`packages/` — real, standalone Composer packages, fully extracted:**
- `zoosper-errors`: `ZoosperException` (extends Marko's `MarkoException`),
  `SensitiveValueRedactor`, `ConsoleExceptionFormatter`,
  `ExceptionDisplayer` (owns all direct Marko error-display integration).
  Depends only on `marko/core` — no other Zoosper module.
- `zoosper-media`: standalone media module (no longer requires
  `zoosper/admin`).

More extractions are planned (see ROADMAP.md §14 — logger is next in
line). New extractions should follow the `zoosper-errors` template:
minimal real dependencies, a `.gitattributes` marking dev-only paths as
`export-ignore`, and co-located tests that never leak into a distributed
package archive.

### Completion gate

Before saying a coding task is complete:

- Check that new PHP files use `declare(strict_types=1);`.
- Check that repositories use prepared statements.
- Check that output from user-authored content is escaped or intentionally sanitised.
- Check that docs mention new commands, routes, database tables, or
  environment variables (including `.env.example`).
- Keep changes phase-sized and easy to review.
- If a fix touches a security-sensitive path (auth, crypto, rate limiting,
  sanitisation), add a regression test that reproduces the actual issue
  being fixed, not just a general-shape test.
