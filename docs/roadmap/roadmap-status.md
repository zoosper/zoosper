# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-18 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.36 | Frontend content_json rendering | block_json pages render from validated content_json through PageRenderer while preserving existing HTML fallback behaviour. |
| 1.37 | Media / upload module | Module-owned media table, admin media library, upload validation, private original storage and controlled public media publishing. |
| 1.37b | Module autoload synchronisation | Composer PSR-4 mappings are generated from enabled module metadata. |
| 1.37c | Admin logout CSRF token hotfix | Admin logout form includes the current CSRF token required by the central admin CSRF middleware. |
| 1.37d | Composer module package readiness | Started staged roadmap and tooling for extracting modules into Composer-installable packages. |
| 1.37d.1 | Package identity bridge | Package tooling now recognises both historical kebab module names and Vendor_Module names. |

## Planned
| Phase | Title |
|---|---|
| 1.37e | Package-manifest generation for first-party modules. |
| 1.37f | Extract `zoosper-media` as first Composer path repository pilot. |
| 1.37g | Editor.js image block integration backed by media assets. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind `RateLimiterInterface`. |
