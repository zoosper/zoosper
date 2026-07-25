# Phase 1.73m-z: Site Lookup Service Binding Finalisation

## Purpose

This phase addresses the remaining Site lookup wiring warning by adding a guarded Site-module service binding for:

```text
SiteLookupInterface -> DatabaseSiteLookup
```

## Safety

- Dry-run by default.
- Exact-shape service config patch only.
- Backup before apply.
- Binding belongs in `app/zoosper-site/config/services.php`, not core runtime source.
- `NullSiteLookup` remains the core fallback only.

## Commands

```bash
php8.5 tools/apply-site-lookup-service-binding.php
php8.5 tools/apply-site-lookup-service-binding.php --apply
php8.5 tools/audit-site-lookup-service-binding.php
```
