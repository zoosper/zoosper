# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-21 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37n.1 | Local media derivative foundation | Local derivative paths and writer are available for future processors. |
| 1.37n.2 | Engine-free local copy/no-op media processor adapter | First concrete processor behind MediaProcessorInterface exists without GD/Imagick coupling. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37n.3 | Upload derivative processing seam | Upload-time derivative dispatch is feature-gated and disabled by default; helper hotfixes make service patching safe. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37n.4 | Enable local copy derivative generation in a controlled admin/browser smoke path. |
| 1.37n.5 | Plan optional `zoosper/media-gd` and `zoosper/media-imagick` processor packages. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface. |
