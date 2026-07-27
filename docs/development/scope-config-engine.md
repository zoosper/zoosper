# Scope Config Engine

## Purpose

Foundation for admin-editable settings scoped to a website, store, or
individual site, mirroring Magento's scope model but adapted to Zoosper's
flattened Site (= store-view) schema — no separate website/store tables
needed; website/store are addressed by the code strings already on Site.

## Resolution order

`ScopeConfigRepository::get()` tries, most specific first:
1. site (Site.id)
2. store (Site.storeCode)
3. website (Site.websiteCode)
4. default

The first scope level with a saved row wins; missing levels are skipped.

## Deliberately separate from ConfigRepository

ConfigRepository = static config/*.php files, aggregated once at boot.
ScopeConfigRepository = dynamic, admin-editable, DB-backed values. Bridging
the two (making static config overridable at runtime) is a future decision,
not part of this phase.

## Status

Engine only (Phase D1). No admin UI yet. Phase D2 will wire one real setting
through it as proof; Phase D3 will build the /admin/settings screen using
Grid Core.
