# Phase 1.70a-l: Site Context Boundary Readiness

## Purpose

This phase starts the Site context boundary decoupling work after the Page fallback boundary cutover.

It is read-only and creates an audit/plan pair for replacing direct core imports of Site module classes with core-owned contracts.

## Commands

```bash
php8.5 tools/audit-site-context-boundary-readiness.php
php8.5 tools/plan-site-context-boundary-cutover.php
```

## Outputs

```text
var/reports/site-context-boundary-readiness.txt
var/reports/site-context-boundary-readiness.json
var/reports/site-context-boundary-cutover-plan.txt
var/reports/site-context-boundary-cutover-plan.json
```

## Intended direction

```text
core SiteContextResolver -> SiteLookupInterface -> Site module DatabaseSiteLookup
```

Instead of:

```text
core SiteContextResolver -> Zoosper\Site\Repository\SiteRepository / Zoosper\Site\Model\Site
```

## Safety

No runtime files are edited in this phase.
