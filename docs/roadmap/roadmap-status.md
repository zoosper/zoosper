# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-18 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37h | Media package transition | Media source, discovery, autoload, tests and tools inventory are stable from the package path. |
| 1.37i | Editor.js media image contracts | Media package owns Editor.js image upload response, config and block sanitisation contracts. |
| 1.37j | Editor.js media upload endpoint wiring | Async Editor.js image upload endpoint is routed to media validation, storage, repository and JSON response contract. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37k | Editor.js image frontend rendering | Managed image blocks in content_json render through PageRenderer using safe /media/ URLs. |

## Planned
| Phase | Title |
|---|---|
| 1.37l | Admin Editor.js Image Tool runtime wiring. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
