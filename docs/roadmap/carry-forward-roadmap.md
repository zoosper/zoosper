# Carry-forward roadmap

## Completed foundations

- Editor.js JSON hidden field and server-side block JSON validation on save.
- Editor.js ContentEditorInterface contract hotfix.
- Editor.js JSON save verifier alignment.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Avoid minimised/compressed PHP, JavaScript, CSS and documentation output.
- Always include meaningful PHPDoc and helpful comments for models, repositories, services, tools and security-sensitive code.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Preserve page SEO metadata fields and the “Search engine optimisation” admin section during page-related refactors.
- Add contract-level verification when implementing or replacing interfaces; syntax checks are not enough.
- Prefer behaviour/contract verification over brittle source-string matching.

## Future TODOs

- Organise long admin page edit forms into clear sections/cards, with progressive enhancement for collapsible sections or tabs.
- Add server-side block renderer integration.
- Add a safe feature flag or migration path to switch selected pages to `content_format=block_json`.
- Add media library with uploads stored outside public first.
- Add quote, delimiter, table and button blocks after renderer contracts are ready.
- Add pagination to all search result grids.
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