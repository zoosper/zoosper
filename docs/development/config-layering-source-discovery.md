# Config Layering Source Discovery

Phase 1.40f-j prepares the first practical migration to the layered config loader.

## Current state

Phase 1.40a-e added the additive merge primitive:

```text
LayeredConfigResult
LayeredConfigLoader
```

This phase intentionally does not change runtime config loading. Instead, it inventories current config sources and produces a migration-readiness report.

## Why discovery first

Zoosper configuration spans many module-owned areas, including routes, services, admin menu, ACL, forms, middleware, events, database schema, and package-specific config. Some config types are high-risk because list semantics matter, especially routes and middleware.

## Candidate safety rules

A good first migration target should be:

- read-only or low-risk;
- associative-array-heavy rather than list-array-heavy;
- not part of request dispatch, auth, CSRF, or routing;
- easy to verify with an audit command and Pest tests;
- useful for module defaults beneath root overrides.

## High-risk config types to avoid first

Avoid starting with:

```text
routes
admin_middleware
middleware
services
```

These can affect request dispatch, auth ordering, or dependency injection behaviour.

## Recommended path

1. Audit all config files and config-loading patterns.
2. Generate a first-migration plan.
3. Choose a low-risk config type.
4. Migrate only that config type to `LayeredConfigLoader`.
5. Add rollback-friendly tests and audit tooling.
