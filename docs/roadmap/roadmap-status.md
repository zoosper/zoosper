# Zoosper CMS — Roadmap Status

Snapshot: 2026-07-17 (AEST).

## Delivered
| Phase | Title | Outcome |
|---|---|---|
| 1.20–1.28 | Entity save lifecycle | Built, activated, and module-discovered listeners are covered by Pest tests. |
| 1.26–1.27 | Thin controllers + central logging | HTML moved into templates where targeted; router catch-and-log safety net added. |
| 1.29 | Schema engine unification | One validated, snapshotted, module-owned schema engine; entity_extension_values fresh-install fix. |
| 1.30 | General event/observer system | Module config/events.php discovery; page publish/unpublish emitters. |
| 1.31 | Module generator CLI | bin/zoosper make:module foundation. |
| 1.32 | Configuration layering | Module config/settings/*.php merged under root config/*.php. |
| 1.33 | Middleware pipeline | PSR-15-style route middleware pipeline; fail-secure authentication and central CSRF guard for admin routes; API routes remain stateless. |
| 1.33b | Route permission OR semantics | Route permission metadata accepts string or list with OR semantics; parity tests guard user/mail routes. |
| 1.33d | Controller middleware cleanup | Redundant controller-level permission/auth/CSRF gates removed from protected admin controllers; middleware owns route-level access decisions. |
| 1.34 | Site-resolution unification | Request-carried SiteContext is the runtime source of truth; page/API/render paths no longer use legacy fallbacks; CurrentSiteContext retired. |
| 1.35 | Router path parameters | Immutable Request route params, static-first parameterised route matching, inline constraints, and module route coverage. |
| 1.36 | Frontend content_json rendering | block_json pages render from validated content_json through PageRenderer while preserving existing HTML fallback behaviour. |
| 1.37 | Media / upload module | Module-owned media table, admin media library, upload validation, private original storage and controlled public media publishing. |
| 1.37b | Module autoload synchronisation | Composer PSR-4 mappings are generated from enabled module metadata, avoiding manual composer.json edits for future modules. |
| 1.40 | Module-owned console commands | config/console.php discovery, make:module console placeholder, and make:command scaffolder. |

## Planned
| Phase | Title |
|---|---|
| 1.37c | Editor.js image block integration backed by media assets. |
| 1.38 | RoleAdminController Latte/template migration. |
| 1.39 | DB-backed rate limiting. |

## Backlog (not urgent)
- 2FA verification after admin login.
- Descriptive exceptions everywhere.
- SchemaSqlBuilder: honour bigint/unsigned auto-increment primary keys.
- Method plugin/interceptor system.
- Route cache/AOT compilation if routing performance requires it later.
