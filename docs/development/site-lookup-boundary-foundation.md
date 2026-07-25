# Phase 1.70m-z: Site Lookup Boundary Foundation

## Purpose

This phase adds the core-owned Site lookup contracts recommended by the Site context cutover plan.

The intended direction is:

```text
core SiteContextResolver -> SiteLookupInterface -> Site module DatabaseSiteLookup
```

instead of core importing:

```text
Zoosper\Site\Repository\SiteRepository
Zoosper\Site\Model\Site
```

## What this phase adds

- `ResolvedSite`
- `SiteLookupInterface`
- `NullSiteLookup`
- `DatabaseSiteLookup`
- architecture tests and audit

## Safety

This is a foundation phase only. It does not rewrite `SiteContextResolver` or `SiteContextResolverFactory`.

## Next phase

Phase 1.71a-l should add a guarded cutover planner for replacing direct SiteRepository/DbSite usage in the core resolver/factory.
