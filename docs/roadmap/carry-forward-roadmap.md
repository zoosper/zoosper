# Carry-forward roadmap

## Completed foundations

- Reduced manual admin translator fallback.
- Admin user locale preference schema/resolver foundation.

## Coding guidelines

- Always produce clean, well-formatted code like PHPStorm Ctrl+Alt+L.
- Avoid minimised/compressed PHP, JavaScript, CSS and documentation output.
- Always include meaningful PHPDoc and helpful comments for models, repositories, services, tools and security-sensitive code.
- Preserve existing fields, admin sections and behaviour during refactors unless removal is explicitly requested.
- Preserve page SEO metadata fields and the “Search engine optimisation” admin section during page-related refactors.
- Keep controllers clean; admin UI sections and processors should be contributed through providers/registries/config where practical.
- Third-party developers must be able to extend/override core behaviour without editing core code.
- Prefer behaviour/contract/rendered-output verification over brittle source-string matching.
- Locale codes used for translation lookup must be validated strictly before they affect file paths.
- Every future phase should include or update one verification runner file so all syntax/check commands can be run with one command and the full output is written to a report file.

## Future TODOs

- Hydrate `admin_users.locale` into the AdminUser model/session flow.
- Wire `AdminUserLocaleResolver` into admin translator resolution when an admin-user context is available.
- Add admin-user locale preference UI.
- Add per-site locale settings from SiteContext/SiteRepository.
- Add persistence helpers/payload consumers for processor-produced values.
- Add replacement/remove/disable rules for section providers via config.
- Add server-side block renderer integration.
- Add a safe feature flag or migration path to switch selected pages to `content_format=block_json`.
- Add media library with uploads stored outside public first.
- Add pagination to all search result grids, e.g. CMS pages, audit logs and login history.
- Add customer login and customer account management.
- CLI local module generator: `php bin/zoosper make:module Vendor/Module`.
