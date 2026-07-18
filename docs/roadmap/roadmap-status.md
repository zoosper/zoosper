# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-18 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37f | Media path repository pilot | `zoosper-media` can be piloted as a Composer path repository. |
| 1.37g | Composer-installed module discovery | ModuleRegistry can discover modules from Composer package metadata and package paths. |
| 1.37h | Media compatibility symlink retirement | `app/zoosper-media` compatibility symlink removed; media is discovered from package/vendor source. |
| 1.37h.1-1.37h.5 | Package test/autoload transition cleanups | Package tests are covered under packages/*/tests/Unit and media package autoload remains mapped. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37h.6 | Tools inventory package workflow classification | Package/module workflow tools are classified as KEEP_OPS. |

## Planned
| Phase | Title |
|---|---|
| 1.37i | Editor.js image block integration backed by media assets. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
