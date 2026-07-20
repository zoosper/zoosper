# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-20 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37r | Media upload cleanup and orphan-file regression coverage | Shared media upload service centralises validation, storage, persistence and cleanup when DB persistence fails after storage succeeds. |
| 1.37r.1 | Media upload cleanup service extraction | Orphan-file cleanup is handled by a dedicated service with behaviour tests and cleanup result diagnostics. |
| 1.37r.1.1 | Media upload cleanup test hotfix | Contract tests assert cleanup-service delegation instead of removed inline helper names. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37r.1.2 | Media upload cleanup contract test hotfix | Cleanup contract test now checks the short cleanup service class name used by same-namespace source. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37r.2 | Migrate normal admin media upload controller to MediaUploadService. |
| 1.37r.3 | Behaviour-level upload controller tests for storage succeeds / DB fails. |
| 1.37n.1 | Local media derivative processor behind MediaProcessorInterface. |
| 1.38 | RoleAdminController Latte/template migration. |
