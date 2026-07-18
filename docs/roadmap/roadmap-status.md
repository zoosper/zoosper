# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-18 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37e | Package manifest generation | First-party source modules carry package-ready composer.json manifests. |
| 1.37f | Media path repository pilot | `zoosper-media` can be piloted as a Composer path repository. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37g | Composer-installed module discovery | ModuleRegistry can discover modules from Composer package metadata as well as app/packages. |

## Planned
| Phase | Title |
|---|---|
| 1.37h | Remove media compatibility symlink after vendor discovery verification. |
| 1.37i | Editor.js image block integration backed by media assets. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind `RateLimiterInterface`. |
