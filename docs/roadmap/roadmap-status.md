# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-19 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37p | Package-aware module scaffolding | `php bin/zoosper make:package-module Vendor/Module` creates Composer-style modules under packages/. |
| 1.37p.1 | Package module scaffolder regex hotfix | Package module names using slash, underscore or dash separators validate correctly. |
| 1.37p.2 | Package module naming hotfix | Camel-cased module names generate kebab-case Composer package names correctly. |
| 1.37p.3 | Package module module-name hotfix | Camel-cased module names preserve class, namespace and test filename casing. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37p.4 | Package module generated filename hotfix | Scaffolded package tests use the generated module class prefix in their filename. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37q | Vendor-installed package discovery audit and docs. |
| 1.37n.1 | Local media derivative processor behind MediaProcessorInterface. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
