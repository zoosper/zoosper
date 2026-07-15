# Modularity Audit - Is Zoosper "Truly Modular"?

*An honest scorecard, 2026-07-15.*

## Rubric

For each concern we ask: **is it module-owned, by what mechanism, and can a
THIRD PARTY extend or override it WITHOUT editing core?**

## Scorecard

| Concern | Module-owned | Mechanism | Extend without touching core? | Notes |
|---|---|---|---|---|
| Routes | ✅ | `config/admin_routes.php`, `config/api_routes.php` | ✅ | Discovered per module |
| Controllers | ✅ | `config/controllers.php` factories | ✅ | Container-resolved |
| Services / DI | ✅ | `config/services.php` + `ServiceContainer` (last-wins `set`/`factory`) | ✅ | A later module can **replace a core binding** - de-facto Magento "preferences" |
| Database schema | ✅ | `config/db_schema.php` -> unified `Schema/` engine (1.29) | ✅ | Validated + snapshot-audited; modules can add columns to another module's table |
| Admin menu | ✅ | `config/admin_menu.php` | ✅ | |
| ACL / permissions | ✅ | `config/acl.php` | ✅ | |
| Admin form sections + processors | ✅ | `AdminFormProviderRegistry` / `AdminFormProcessorRegistry` + `AdminFormConfigAggregator` | ✅ | Add/replace/reorder sections; add validation |
| Admin form UI fields | ✅ | `config/admin_ui.php` via `AdminFormUiConfigLoader` (fields/remove/replace/inject) | ✅ | Field-level injection into existing forms |
| Entity-save lifecycle listeners | ✅ | `config/entity_save_listeners.php` (1.28) | ✅ | Validate/mutate/abort a save |
| Logging targets | ✅ | `config/logging.php` | ✅ | |
| Templates / views | ✅ | Theme override + module `resources/views` (`module::` namespace) | ✅ | Theme can override a module template |
| Admin assets | ✅ | `config/admin_assets.php` | ✅ | |
| i18n translations | ✅ | `i18n/{locale}.php` | ✅ | |
| **General application events (non-save)** | ❌ | - | **NO** | **GAP - Phase 1.30** |
| **Method plugins / interceptors** | ❌ | - | **NO** | **GAP - later** (Magento around/before/after) |
| **Root config values** | ⚠️ Partial | `ConfigRepository::fromPath` loads **root `config/` only** | **Partial** | Modules can't ship merged config **defaults** - **GAP: config layering** |
| **CLI module scaffolding** | ❌ | - | **NO** | **DX GAP - Phase 1.31** |
| Frontend content blocks (Editor.js `content_json`) | ⚠️ Partial | Stored + validated (`BlockJsonValidator`) | Partial | Not rendered on the frontend yet |
| Media / file management | ❌ | - | NO | Missing feature (future module) |
| Site / store context | ⚠️ Partial | `CurrentSiteContext` + `SiteContextResolver` in core | Partial | A second/legacy site path also exists - a **duality to unify** (1.34) |

## Verdict: ~70% true modular

The dimensions that are **done are the load-bearing ones.** A module genuinely
owns its **routes, controllers, DI (including overriding core), schema, admin
surface, ACL, logging, templates and i18n**, and can hook the **entity save
lifecycle** - all by dropping config files, no core edits. That is already more
disciplined than most PHP CMSes.

The missing **~30%** is precisely what turns *"many targeted extension points"*
into *"extend **anything**"*:

1. **A general event bus** - react to *any* action, not just saves. *(1.30)*
2. **Method plugins / interceptors** - alter behaviour in code you don't own,
   without replacing the whole service. *(later)*
3. **Config layering** - ship sensible defaults from a module; let root override.
   *(1.32)*
4. **A module generator** - so "drop in a folder" starts from one command. *(1.31)*

## What "true modular" will mean when done

A checklist a third-party developer can satisfy:

1. **Scaffold** a module with one command. - ❌ today (1.31)
2. **Add** tables, routes, admin screens, services by dropping config. - ✅ today
3. **React** to any core/other-module action via events. - ❌ today (1.30)
4. **Alter** another module's behaviour without editing it. - ⚠️ partial today
   (full service override via DI ✅; fine-grained method plugins ❌)
5. **Ship** config defaults. - ❌ today (1.32)
6. **Install/remove** by adding/deleting a folder. - ✅ today

**Score: 2.5 / 6 fully done, the rest partial or roadmapped.** The honest claim
today is *"deeply modular, with a clear, funded path to fully modular."* Phases
1.30-1.32 close most of the gap; the plugin/interceptor system finishes it.
