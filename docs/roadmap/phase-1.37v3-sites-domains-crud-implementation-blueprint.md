# Phase 1.37v.3 — Sites and Site Domains CRUD Implementation Blueprint

## Purpose

Prepare the final source-specific implementation pass for Sites and Site Domains admin CRUD.

The previous 1.37v phases established the CRUD contract and added source-inspection tooling. This phase locks the exact implementation blueprint so the next build can write controllers, routes, services and templates without changing scope mid-flight.

## Required admin routes

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

## Required permission seam

```text
site.manage
```

If `site.manage` is not yet available in the ACL tree, the implementation may temporarily use the nearest existing admin permission, but it must document the follow-up parity change.

## Sites admin CRUD target

The Sites admin screen should support:

```text
- list existing sites
- create site
- edit site
- validate name
- validate code/key
- validate status
- optionally capture default_locale and theme_code if the schema supports them
- preserve existing SiteRepository and SiteContextResolver behaviour
```

## Site Domains admin CRUD target

The Site Domains admin screen should support:

```text
- list domains
- create domain
- edit domain
- assign domain to site_id
- validate host
- validate optional path_prefix
- validate is_primary
- validate status
```

## Empty state behaviour

When there are no rows, both screens should render useful launch-readiness empty states:

```text
No sites exist yet. Create your first site to start publishing.
No site domains exist yet. Add a domain to route requests to a site.
```

## Non-goals

```text
- tenant billing
- multi-database tenancy
- DNS verification
- website/store/store-view hierarchy
- settings UI
- theme assignment UI beyond field preservation if already available
```

## Implementation requirement

Before generating source changes, run:

```bash
php8.5 tools/inspect-sites-domains-implementation-targets.php
```

Use the output to match current route config, controller factory, layout/template and repository conventions.
