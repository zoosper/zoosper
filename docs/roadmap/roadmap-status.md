# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-21 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37n.1 | Local media derivative foundation | Local derivative paths and writer are available for future processors. |
| 1.37r.7 | Media upload repository failure cleanup test | Concrete fixture proves stored files are cleaned when repository persistence fails. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37n.2 | Engine-free local copy/no-op media processor adapter | Adds first concrete processor behind MediaProcessorInterface without GD/Imagick coupling. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37n.3 | Connect media derivative processing to upload flow behind a feature/policy seam. |
| 1.37n.4 | Plan optional `zoosper/media-gd` and `zoosper/media-imagick` processor packages. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
