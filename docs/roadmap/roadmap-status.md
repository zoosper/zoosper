# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-21 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.37r.5 | Media upload failure-path audit | Package-local audit locks the shared upload service failure-path contract across normal admin and Editor.js uploads. |
| 1.37r.5.1 | Media upload failure-path audit hotfix | Audit source-string probes escape `$storedPath` and correctly verify public `/media/...` mapping. |
| 1.37r.5.2 | Media upload failure-path test hotfix | Regression test was updated to avoid `$storedPath` interpolation warnings. |

## Current
| Phase | Title | Outcome |
|---|---|---|
| 1.37r.5.3 | Media upload failure-path test literal hotfix | Remaining Pest warning is removed by using a single-quoted literal assertion. |

## Planned near-term
| Phase | Title |
|---|---|
| 1.37r.6 | Integration-style repository failure test if concrete classes allow safe setup. |
| 1.37t.3 | Move first media docs batch into packages/zoosper-media/docs with root link stubs. |
| 1.37n.1 | Local media derivative processor behind MediaProcessorInterface. |
| 1.38 | RoleAdminController Latte template migration. |
