# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-18 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37 | Media / upload module | Module-owned media table, admin media library, upload validation, private original storage and controlled public media publishing. |
| 1.37b | Module autoload synchronisation | Composer PSR-4 mappings are generated from enabled module metadata. |
| 1.37c | Admin logout CSRF token hotfix | Admin logout form includes the current CSRF token required by the central admin CSRF middleware. |
| 1.37d | Composer module package readiness | Started staged roadmap and tooling for Composer-installable packages. |
| 1.37e | Package manifest generation | First-party source modules carry package-ready composer.json manifests. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37f | Media path repository pilot | Pilot `zoosper-media` extraction to `packages/zoosper-media` using a Composer path repository and compatibility symlink. |

## Planned
| Phase | Title |
|---|---|
| 1.37g | Composer-installed module discovery without app symlink. |
| 1.37h | Editor.js image block integration backed by media assets. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind `RateLimiterInterface`. |
