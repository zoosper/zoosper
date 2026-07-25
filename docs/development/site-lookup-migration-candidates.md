# Site Lookup Migration Candidates

Generated: 2026-07-25T08:46:14+00:00

This tracker records consumers that still reference the concrete `SiteRepository` outside approved infrastructure/admin areas. It exists so migration work is durable and never forgotten between phases.

- Total candidates: 4
- Auto-migratable (clean constructor swap): 2
- Manual review: 2

## Auto-migratable candidates

- [ ] `app/zoosper-admin/src/Controller/PageAdminController.php` — Clean constructor type-hint swap candidate for the assisted helper.
- [ ] `app/zoosper-admin/src/Controller/ThemeAdminController.php` — Clean constructor type-hint swap candidate for the assisted helper.

## Manual review candidates

- [ ] `app/zoosper-api/src/Controller/ContentPageController.php` — Manual review recommended before migrating.
- [ ] `app/zoosper-page/src/Controller/PageController.php` — Manual review recommended before migrating.

## How to progress this list

1. Run `php8.5 tools/assist-site-lookup-migration.php` for a dry-run preview.
2. Review the proposed swaps.
3. Run `php8.5 tools/assist-site-lookup-migration.php --apply` to migrate clean cases.
4. Handle manual candidates deliberately in a later cleanup phase.
