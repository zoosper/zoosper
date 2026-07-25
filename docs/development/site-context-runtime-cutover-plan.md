# Phase 1.71a-l: Site Context Runtime Cutover Plan

## Purpose

This phase creates a live-file-aware cutover draft for moving `SiteContextResolver` and `SiteContextResolverFactory` from direct Site module imports to the core-owned `SiteLookupInterface` boundary.

It does not edit runtime PHP files yet.

## Commands

```bash
php8.5 tools/plan-site-context-runtime-cutover.php
php8.5 tools/audit-site-context-runtime-cutover-plan.php
```

## Outputs

```text
var/reports/site-context-runtime-cutover-plan.txt
var/reports/site-context-runtime-cutover-plan.json
var/reports/site-context-runtime-cutover-draft.patch.md
var/reports/site-context-runtime-cutover-plan-audit.txt
```

## Intended cutover direction

```text
SiteContextResolver -> SiteLookupInterface -> DatabaseSiteLookup in zoosper-site
```

## Safety

No runtime files are changed in this phase.
