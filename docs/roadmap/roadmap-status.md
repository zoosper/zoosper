# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-18 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37f | Media path repository pilot | `zoosper-media` can be piloted as a Composer path repository. |
| 1.37g | Composer-installed module discovery | ModuleRegistry can discover modules from Composer package metadata and package paths. |
| 1.37h | Media compatibility symlink retirement | `app/zoosper-media` compatibility symlink removed; media is discovered from package/vendor source. |
| 1.37h.1 | Package testsuite discovery | Root PHPUnit/Pest config includes package tests so extracted modules remain covered. |
| 1.37h.2 | Package testsuite tool hotfix | Package testsuite sync tool fixed after parse failure. |
| 1.37h.3 | Package module autoload hotfix | Module autoload sync discovers packages/*/module.php after symlink removal. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37h.4 | Package testsuite normalisation | Package test discovery narrows to packages/*/tests/Unit to avoid duplicate-suite warnings. |

## Planned
| Phase | Title |
|---|---|
| 1.37i | Editor.js image block integration backed by media assets. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
