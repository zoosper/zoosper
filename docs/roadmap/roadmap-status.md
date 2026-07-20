# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-20 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37r.2 | Media admin upload migration readiness | Audit and source-dump tools prepare `MediaAdminController::upload()` migration to `MediaUploadService`. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37r.2.1 | Media admin upload readiness test hotfix | Readiness test now permits `.env` in a safety note while still preventing it from being listed as a dump target. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37r.3 | Migrate normal admin media upload controller to MediaUploadService. |
| 1.37r.4 | Behaviour-level upload controller tests for storage succeeds / DB fails. |
| 1.37n.1 | Local media derivative processor behind MediaProcessorInterface. |
| 1.38 | RoleAdminController Latte/template migration. |
