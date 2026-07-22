# Sites and Site Domains Controller Blueprint

## Controller direction

The launch-readiness CRUD implementation should follow existing Zoosper admin controller conventions rather than introducing a separate CRUD framework.

Expected controller shape:

```text
SiteAdminController or SitesAdminController
SiteDomainAdminController or SiteDomainsAdminController
```

## Suggested actions

```text
index/list
create
edit
save
```

If the current route convention prefers separate `store` or `update` actions, follow the existing convention discovered by the inspection output.

## Dependencies

Reuse current repositories/services where possible:

```text
SiteRepository
SiteContextResolver/SiteRepository conventions
SiteDomainRepository if it already exists
```

If `SiteDomainRepository` does not yet exist, implement the smallest repository needed for list/create/update under the site module.

## Rendering

Prefer existing admin rendering conventions:

```text
AdminLayout
AdminViewRenderer
Latte template if module already uses templates
safe inline readiness HTML only if that is still the current convention
```

Do not introduce a new rendering subsystem during this phase.
