# Carry-forward roadmap

## Completed foundations

- Module admin form config aggregation foundation.
- Admin form processors foundation.
- Admin form processors config hotfix.
- Empty admin form processor handle aggregation hotfix.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Avoid minimised/compressed PHP, JavaScript, CSS and documentation output.
- Always include meaningful PHPDoc and helpful comments for models, repositories, services, tools and security-sensitive code.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Preserve page SEO metadata fields and the “Search engine optimisation” admin section during page-related refactors.
- Keep controllers clean; admin UI sections and processors should be contributed through providers/registries/config where practical.
- Third-party developers must be able to extend/override core behaviour without editing core code.
- Prefer behaviour/contract/rendered-output verification over brittle source-string matching.
- Preserve empty config handles when they intentionally document extension points.

## Future TODOs

- Wire admin form processors into page create/update flows with clear validation error handling.
- Add replacement/remove/disable rules for section providers via config.
- Add progressive enhancement for collapsible admin form sections or tabs if the page form becomes much longer.
- Add server-side block renderer integration.
- Add a safe feature flag or migration path to switch selected pages to `content_format=block_json`.
- Add media library with uploads stored outside public first.
- Add quote, delimiter, table and button blocks after renderer contracts are ready.
- Add pagination to all search result grids, e.g. CMS pages, audit logs and login history.
- CLI local module generator: `php bin/zoosper make:module Vendor/Module`.
- Static asset command: `php bin/zoosper static:publish`.
- Consolidate developer tools into stable CLI commands.
- Vite/Tailwind asset pipeline.
- Admin Role Page Refactor.
- Cache Manager Design.
- CDN provider/purge adapter.
- Index Manager.
- Documentation website build pipeline.
