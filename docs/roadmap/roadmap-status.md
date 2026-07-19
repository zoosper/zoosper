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
| 1.37n | Media processing policy and derivative architecture | Media derivative profiles, processing policy and processor contract are defined without adding synchronous image processing. |
| 1.37o | Media standalone package readiness | `zoosper/media` has package metadata, standalone test config, docs and audit tooling for future repository extraction. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37o.1 | Media package metadata test path hotfix | Package-local composer metadata test reads the package composer.json from the correct directory. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37p | Package-aware module scaffolding for packages/ output. |
| 1.37n.1 | Local media derivative processor behind MediaProcessorInterface. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
