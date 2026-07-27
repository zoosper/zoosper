# Grid Core

## Problem

Pagination controls were missing on Audit Log / Login History because their
templates only ever echoed `rows`; nothing consumed the `pagination`/
`criteria` data added when pagination was first introduced. More broadly,
every admin grid was reinventing its own Criteria class and template, which
does not scale as more grids (Media, Site Domains, URL Rewrites...) are added.

## Design

One shared system in `Zoosper\Core\Grid`:
- `GridDefinition` — declarative columns/filters/sort for a screen (PHP class,
  not XML — IDE-autocompletable, one file to read, Filament-inspired rather
  than Magento UI Components).
- `GridCriteria` — generic pager + sort + filter bag, parsed from request
  values via `fromValues()`.
- `GridDataSourceInterface` — the one method (`paginate`) a repository
  implements to plug into the system.
- `GridHtmlRenderer` — the one renderer that produces filter bar + sortable
  table + pagination HTML for any grid.

## Retrofit

Audit Log and Login History were rebuilt onto this system, superseding their
Phase 1.112 bespoke Criteria classes entirely (deleted, not deprecated) per the
explicit "no backward compatibility, redesign for the best solution" direction.

## Status / next steps

- CSS not yet wired into the asset pipeline (need admin_assets.php).
- Pages admin grid (PageGridCriteria/PageGridRepository) has NOT been migrated
  onto this yet — a natural next phase once this is proven in production.
- This is the foundation the Global Search and Scope Config RFC ideas build on.
