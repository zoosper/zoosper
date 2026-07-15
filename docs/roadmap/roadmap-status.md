# Zoosper CMS - Roadmap Status

**Snapshot:** 2026-07-15 (evening).

## Delivered (this arc)

| Phase | Title | Outcome |
|---|---|---|
| 1.20 | Entity Save Lifecycle Events | Foundation classes (dispatcher, context, runner, 7 stage constants). |
| 1.21 | Pest harness + first regression suite | Co-located per-module tests; lifecycle locked in. |
| 1.22 | Foundation consolidation | 36 dead one-shot files removed; tools inventory baseline. |
| 1.23 | Lifecycle integration foundation + docs | Runner lock-in tests; architecture + contributor docs. |
| 1.24 | Controllers wired to runner | Page + AdminUser save flows delegate to the runner. |
| 1.25 / 1.25b | Lifecycle activated + real validation | Dispatcher/runner in DI; `PageSaveValidationListener`; controllers injected. |
| 1.26 / 1.26a | Thin controllers + Latte | `UserAdminController` thinned to templates; PSR-4 path fix. |
| 1.27 | Central exception logging + thin PageAdminController | `Router` catch-and-log safety net; page controller thinned. |
| 1.28 | Module-discovered save listeners | `config/entity_save_listeners.php` per module. |
| 1.29 | Schema engine unification | One validated, snapshotted, module-owned engine; folded `database/schema/*`; fixed `entity_extension_values` fresh-install bug. |

**Metrics:** 59 Pest tests / 125 assertions; `MIGRATE_TO_PEST` = 81; ~45 legacy
files retired; two fresh-install landmines fixed and guarded by DB-backed tests.

## Planned

| Phase | Title |
|---|---|
| 1.30 | **General module Event/Observer bus** (`config/events.php`) |
| 1.31 | Module generator CLI (`bin/zoosper make:module`) |
| 1.32 | Config layering (module defaults + root overrides) |
| 1.33 | Router path parameters (unlocks pretty/SEO URLs) |
| 1.34 | Site-resolution unification (`SiteContext`) |
| 1.35 | Frontend `content_json` (Editor.js block) rendering |
| 1.36 | Media / file-management module |
| 1.37 | Method plugins / interceptors (around/before/after) |
| -    | Documentation website (generated from these Markdown docs) |

## Backlog (not urgent)

- 2FA verification **after** admin login (enrolment/reset infra already exists).
- Descriptive exceptions **everywhere** (extend the `ZoosperException` pattern).
- `SchemaSqlBuilder` honour `bigint`/`unsigned` for auto-increment primary keys
  (currently hardcodes `INT`).

## Standing rules (carry-forward)

- **Co-located, self-contained module tests** - each module owns `<module>/tests/`;
  no root `tests/` folder.
- **Pest run convention** - root `phpunit.xml`; run from repo root with **no `-c`
  flag** (Pest v3 mis-parses `--cache-directory`); **built-in expectations only**
  in co-located module `Pest.php`; **no `ReflectionProperty::setAccessible()`** on
  PHP 8.1+ (deprecated in 8.5).
- **Every behavioural change ships a Pest test.**
- **Coverage never goes down** - retire a `verify-*` only once a Pest equivalent
  exists.
- **Docs updated every phase** (for the future docs website).
- **PCI-aware** - never log secrets/tokens/payment data.
- **Thin controllers; HTML in Latte; business logic in services.**
- **Full-file drop-ins, not patches; no backward-compat shims (pre-release).**
- **PHP class file paths must match composer PSR-4** (e.g. `Zoosper\Admin\` ->
  `app/zoosper-admin/src/`).

## Key metric

`MIGRATE_TO_PEST` (old `verify-*` scripts remaining) trending to **0**. Track with
`php bin/tools-inventory.php`.
