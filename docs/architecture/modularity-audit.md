# Modularity Audit - Is Zoosper "Truly Modular"?

*An honest scorecard, 2026-07-15.*

## Rubric

For each concern we ask: **is it module-owned, by what mechanism, and can a third party extend or override it without editing core?**

## Scorecard

| Concern | Module-owned | Mechanism | Extend without touching core? | Notes |
|---|---|---|---|---|
| Routes | ✅ | `config/admin_routes.php`, `config/api_routes.php` | ✅ | Discovered per module |
| Controllers | ✅ | `config/controllers.php` factories | ✅ | Container-resolved |
| Services / DI | ✅ | `config/services.php` + `ServiceContainer` | ✅ | A later module can replace a core binding. |
| Database schema | ✅ | `config/db_schema.php` -> unified `Schema/` engine | ✅ | Validated + snapshot-audited; modules can add columns to another module's table. |
| Admin menu | ✅ | `config/admin_menu.php` | ✅ | |
| ACL / permissions | ✅ | `config/acl.php` | ✅ | |
| Admin form sections + processors | ✅ | Registry/config aggregation | ✅ | Add/replace/reorder sections; add validation. |
| Admin form UI fields | ✅ | `config/admin_ui.php` | ✅ | Field-level injection into existing forms. |
| Entity-save lifecycle listeners | ✅ | `config/entity_save_listeners.php` | ✅ | Validate/mutate/abort a save. |
| Logging targets | ✅ | `config/logging.php` | ✅ | |
| Templates / views | ✅ | Theme override + module `resources/views` (`module::` namespace) | ✅ | Theme can override a module template. |
| Admin assets | ✅ | `config/admin_assets.php` | ✅ | |
| i18n translations | ✅ | `i18n/{locale}.php` | ✅ | |
| General application events | ✅ | `config/events.php` | ✅ | Added after the original audit. |
| Root config values | ✅ | Module `config/settings/*.php` layered under root config | ✅ | Added after the original audit. |
| CLI module scaffolding | ✅ | `bin/zoosper make:module` and `make:command` | ✅ | Added after the original audit. |
| Method plugins / interceptors | ❌ | - | NO | Later roadmap item. |
| Frontend content blocks (Editor.js `content_json`) | ⚠️ Partial | Stored + validated (`BlockJsonValidator`) | Partial | Not rendered on the frontend yet. |
| Media / file management | ❌ | - | NO | Missing feature (future module). |
| Site / store context | ✅ | `SiteContextResolver` + request-carried `Request::siteContext()` | ✅ | Phase 1.34 unified the render/page hot path around explicit site context. |

## Current verdict

Zoosper is now substantially closer to the original "true modular" target than the 2026-07-15 audit baseline. The remaining major architecture gap is the method plugin/interceptor system; product-feature gaps such as media management and frontend block rendering remain roadmapped.
