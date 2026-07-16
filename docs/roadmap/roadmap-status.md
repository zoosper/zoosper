# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-15 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.20–1.28 | Entity save lifecycle | Built, activated, module-discovered listeners |
| 1.26–1.27 | Thin controllers + central logging | HTML in Latte; Router catch-and-log |
| 1.29 | Schema engine unification | One validated, snapshotted, module-owned engine; entity_extension_values fresh-install fix |
| 1.30 | General event/observer system | Module config/events.php discovery; page publish/unpublish emitters |
| 1.31 | Module generator CLI | bin/zoosper make:module + bin/verify runner |
| 1.32 | Configuration layering | Module config/settings/*.php merged under root config/*.php |
| 1.33 | **Middleware pipeline** | PSR-15-style pipeline in ModuleRouteLoader; fail-secure AuthenticationMiddleware (1.33a/b) + CsrfMiddleware (1.33c); admin routes only, API untouched |

> Correction: an earlier snapshot listed 1.33 as "Router path parameters". That
> was stale. 1.33 shipped as the middleware pipeline + auth/CSRF guards. Router
> path parameters are re-planned as Phase 1.35.

## Planned
| Phase | Title |
|---|---|
| 1.33d | Remove now-redundant controller-level auth/CSRF checks (belt-and-braces cleanup) |
| 1.34 | Site-resolution unification (SiteRepository = source of truth; SiteContext = resolution layer) |
| 1.35 | Router path parameters (/admin/pages/{id}) |
| 1.36 | Wire content_json to frontend rendering (BlockJsonToHtmlRenderer in PageRenderer) |
| 1.37 | Media / upload module (first real third-party module using every extension point) |

## Backlog (not urgent)
- 2FA verification after admin login.
- Descriptive exceptions everywhere.
- SchemaSqlBuilder: honour bigint/unsigned auto-increment primary keys.
- Rate limiting.