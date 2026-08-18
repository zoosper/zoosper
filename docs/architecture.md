# Architecture

Zoosper is an API-first modular CMS built on Marko components and PHP 8.5.

## Runtime

The application loads layered configuration, discovers modules, composes services, loads module routes and executes middleware before dispatching controllers. Site context is resolved once per request and carried through the request path.

## Modules

Modules own routes, controllers, services, migrations, schema declarations, permissions, Admin menus, Admin assets, templates, translations and extension contracts. Installed Composer modules opt in explicitly through package metadata.

## Configuration

Module defaults are loaded below project overrides. Secrets remain environment-owned. Settings can expose configuration without becoming the source of truth for every value.

## Persistence

Repositories own database access. Migrations are module-owned and discovered through the module registry. SQLite is supported for local development and automated fresh-install proof; MySQL is the production target.

## Presentation

Latte is the current default template engine, not a platform-wide restriction. The architecture remains API-first and template-engine integration should remain replaceable.

## Security

Authentication, CSRF, route permissions, two-factor authentication, security headers, sanitisation, rate-limiting seams and audit logging are explicit runtime boundaries.

## Logging boundary

Logging is owned by the standalone `zoosper/logger` package. Core and feature consumers use the native Zoosper logger boundary, while the package delegates physical writes to Marko `FileLogger` with `DailyRotation`. Module `config/logging.php` contributions remain discoverable and retain their logical channel and legacy filename identities. Root Composer owns `zoosper/logger`; the package owns `marko/log` and `marko/log-file`.

### Feature lifecycle APIs
Feature modules own lifecycle routes and response mapping. Page archive, restore and guarded permanent deletion use Page-owned application/domain boundaries; Auth owns only PAT identity and scope validation.
