# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-19 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37h | Media package transition | Media source, discovery, autoload, tests and tools inventory are stable from the package path. |
| 1.37i | Editor.js media image contracts | Media package owns Editor.js image upload response, config and block sanitisation contracts. |
| 1.37j | Editor.js media upload endpoint wiring | Async Editor.js image upload endpoint is routed to media validation, storage, repository and JSON response contract. |
| 1.37k | Editor.js image frontend rendering | Managed image blocks in content_json render through PageRenderer using safe /media/ URLs. |
| 1.37l | Admin Editor.js Image Tool runtime wiring | Admin editor emits Image Tool config, CSRF headers and browser runtime wiring for media uploads. |
| 1.37m | Media/editor browser smoke and UX polish | Browser smoke checklist, runtime diagnostics and admin image-block CSS polish. |
| 1.37m.1 | Editor.js upload permission hotfix | Page managers can use the page editor image upload endpoint without full media-library management permission. |
| 1.37m.2 | Media schema runtime diagnostic | Missing live media_assets table is diagnosed with clear migration instructions. |
| 1.37m.3 | Schema CURRENT_TIMESTAMP default hotfix | Declarative schema emits CURRENT_TIMESTAMP defaults as SQL expressions instead of quoted strings. |
| 1.37m.4 | Editor.js image validation hotfix | Uploaded image blocks are accepted by server-side content_json validation when they use managed /media/ URLs. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37m.5 | Editor.js image validation parse hotfix | BlockJsonValidator parses correctly after image validation support. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37n | Media processing policy and derivative architecture. |
| 1.37o | Prepare zoosper/media for true standalone repository workflow. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
