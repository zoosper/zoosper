# Phase 1.37u — Admin sidebar route integrity and launch readiness stubs

This phase starts implementation of the Launch Readiness Arc.

## Outcome

```text
- Adds durable audit tooling for dead core admin sidebar links.
- Records concrete launch-readiness targets for Sites, Site Domains and Settings.
- Adds documentation stubs for launch-readiness admin routes.
- Adds tests for the navigation tooling contract and temporary-helper cleanup policy.
```

## Target links

```text
Site Domains -> /admin/site-domains
Sites        -> /admin/sites
Settings     -> /admin/settings
```

## Verification

```bash
php8.5 tools/audit-admin-launch-readiness-navigation.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Routing/AdminLaunchReadinessNavigationTest.php
PHP=php8.5 bin/verify
```

## Next

```text
Phase 1.37v — Sites and site domains admin CRUD
```
