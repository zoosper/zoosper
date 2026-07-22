# Launch Readiness Admin Navigation Operations

## Target phase

```text
Phase 1.37u — Admin sidebar route integrity and launch readiness stubs
```

## Manual inspection before build

Inspect the sidebar source/template and confirm there are no intended permanent `href="#"` links for core CMS areas.

Current known targets:

```text
Site Domains -> /admin/site-domains
Sites        -> /admin/sites
Settings     -> /admin/settings
```

## Suggested verification commands after build

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Routing/AdminSidebarRouteIntegrityTest.php
```

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Expected result

```text
PASS composer dump-autoload
PASS pest
PASS schema validate
PASS tools inventory
```

## Build notes

Keep the first implementation additive and low-risk:

```text
- add real routes
- add safe readiness pages
- test the route/navigation contract
- avoid full CRUD until Phase 1.37v
```
