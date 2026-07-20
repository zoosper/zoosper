# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-20 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37q | Vendor package discovery audit and docs | Vendor-installed module discovery contract, audit tooling and fixture tests are documented. |
| 1.37q.1 | Vendor discovery fixture hotfix | Vendor package discovery fixture tests include Composer installed.php metadata consistently. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37r | Media upload cleanup and orphan-file regression coverage | Shared media upload service centralises validation, storage, persistence and cleanup when DB persistence fails after storage succeeds. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37r.1 | Migrate normal admin media upload controller to MediaUploadService. |
| 1.37r.2 | Behaviour-level upload controller tests for storage succeeds / DB fails. |
| 1.37n.1 | Local media derivative processor behind MediaProcessorInterface. |
| 1.38 | RoleAdminController Latte/template migration. |
