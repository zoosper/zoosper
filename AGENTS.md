# Zoosper Agent Guidelines

<!-- BEGIN marko:devai -->
Zoosper is a PHP 8.5+ CMS built with Marko-inspired modular architecture.

## Project rules

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

## Current modules

- `zoosper-core`: bootstrap, config, database, routing, HTTP, security utilities.
- `zoosper-auth`: admin users, roles, permissions, login/session services.
- `zoosper-admin`: admin HTTP controllers.
- `zoosper-api`: API controllers and JSON response shape.
- `zoosper-site`: site/domain resolution.
- `zoosper-page`: page entity, repository, rendering and frontend routing.

## Completion gate

Before saying a coding task is complete:

1. Check that new PHP files use `declare(strict_types=1);`.
2. Check that repositories use prepared statements.
3. Check that output from user-authored content is escaped or intentionally sanitised.
4. Check that docs mention new commands, routes or database tables.
5. Keep changes phase-sized and easy to review.
<!-- END marko:devai -->
