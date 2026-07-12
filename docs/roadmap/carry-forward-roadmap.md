# Carry-forward roadmap

## Completed foundations

- Page repository dual content hydration foundation.
- Page SEO metadata restoration and admin SEO section recovery.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Avoid minimised/compressed PHP, JavaScript, CSS and documentation output.
- Always include meaningful PHPDoc and helpful comments for models, repositories, services, tools and security-sensitive code.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Preserve page SEO metadata fields and the “Search engine optimisation” admin section during page-related refactors.

## Future TODOs

- Add Editor.js JSON hidden field and server-side block_json validation on save.
- Add server-side block renderer integration.
- Add media library with uploads stored outside public first.
- Add quote, delimiter, table and button blocks after renderer contracts are ready.
- CLI local module generator: `php bin/zoosper make:module Vendor/Module`.
- Static asset command: `php bin/zoosper static:publish`.
- Consolidate developer tools into stable CLI commands.
- Vite/Tailwind asset pipeline.
- Admin Role Page Refactor.
- Cache Manager Design.
- CDN provider/purge adapter.
- Index Manager.
- Documentation website build pipeline.
- Add pagination to all search result grids. e.g. CMS page, audit logs, login history etc.