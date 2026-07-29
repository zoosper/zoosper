# Phase 1.41: Admin/Module Dependency Decoupling

## Goal

Three feature modules — `zoosper-two-factor`, `zoosper-media`, and
`zoosper-page` — required `zoosper/admin` in their `composer.json`, purely
because their admin-facing controllers depended on a handful of
`zoosper-admin`'s **concrete** classes (`AdminLayout`, `AdminViewRenderer`,
`AuditLogger`, `LoginHistoryRepository`, and the admin-form composition
classes). This meant none of the three could be installed, tested, or
extracted as a standalone package without also installing all of
`zoosper-admin`.

The fix follows the exact same pattern already proven for site resolution in
an earlier phase: `Zoosper\Core\Site\SiteLookupInterface` + `NullSiteLookup`,
with `zoosper-site` providing the real `DatabaseSiteLookup` implementation.
Define a small interface for "the thing this concrete class actually does",
put the interface somewhere the dependent module can safely reach (Core, or
Auth when the contract genuinely needs `AdminUser`), have `zoosper-admin`'s
real class implement it additively, and have the dependent module's code
depend on the interface instead of the concrete class.

## What was done

### `zoosper-two-factor` — fully decoupled

- `Zoosper\Core\Audit\AuditLoggerInterface` — new. `AuditLogger` implements it
  via a new, additive `logAction()` method (its original `record()` method,
  used elsewhere with a full `AdminUser`, is untouched).
- `Zoosper\Core\Audit\LoginHistoryRecorderInterface` — new.
  `LoginHistoryRepository` implements it; no signature changes needed, its
  existing `record()` method already matched exactly.
- `Zoosper\Auth\Layout\AdminLayoutRendererInterface` — new, but placed in
  `zoosper-auth`, not `zoosper-core`. This interface's `render()` signature
  genuinely needs `?AdminUser $user`, and `zoosper-core`'s own
  `CoreDecouplingArchitectureTest` correctly forbids Core from depending on
  *any* feature module, including Auth. Since every consumer of this
  interface (`zoosper-admin`, `zoosper-two-factor`) already requires
  `zoosper/auth`, Auth is the correct shared home — this introduces no new
  module dependency.
- **Bug found and fixed along the way**: `AdminTwoFactorResetService`'s audit
  logging call passed an `int` where `AuditLogger::record()` required a full
  `?AdminUser`, and omitted the required `summary` parameter. Under
  `strict_types=1` this threw a `TypeError` on every call, silently swallowed
  by a broad `catch (Throwable)` — meaning 2FA reset actions were **never**
  actually being written to the audit log. The new `logAction()` interface
  method has parameter types that match what the service actually has
  available, fixing this correctly rather than papering over it.
- Result: `zoosper-two-factor/composer.json` no longer lists `zoosper/admin`
  in `require`.

### `zoosper-media` — fully decoupled

- `Zoosper\Auth\UI\AdminViewRendererInterface` — new (same reasoning as
  `AdminLayoutRendererInterface` above — needs `AdminUser`, lives in Auth).
  `AdminViewRenderer` implements it additively.
- **Bonus finding**: `MediaAdminController`'s `AdminLayout` (`$layout`)
  constructor parameter was never referenced anywhere in the class's method
  bodies — every render call already went through `$views`
  (`AdminViewRenderer`), not `$layout` directly. This was dead code, not just
  a coupling problem, so it was removed entirely rather than decoupled.
- Result: `packages/zoosper-media/composer.json` no longer lists
  `zoosper/admin` in `require`.

### `zoosper-page` — partially decoupled (see "What was deliberately not
done" below)

- `PageAdminController` updated to use `AdminLayoutRendererInterface` and
  `AdminViewRendererInterface` (both already proven above).
- Nine of eleven admin-form classes relocated from `zoosper-admin`'s form
  namespace to `Zoosper\Core\Form`:
  `AdminFormSection`, `AdminFormSectionProviderInterface`,
  `AdminFormProviderRegistry`, `AdminFormRenderer`,
  `AdminFormProcessorInterface`, `AdminFormProcessingResult`,
  `AdminFormProcessorRegistry`, `AdminFormConfigProviderFactory`,
  `AdminFormProcessorConfigFactory`.
- Confirmed via a repo-wide grep that only `zoosper-page`'s four form section
  providers (`PageDetailsSectionProvider`, `PageContentSectionProvider`,
  `PageSeoSectionProvider`, `PagePublishingSectionProvider`) used these nine
  classes — no other admin controller (`RoleAdminController`,
  `UserAdminController`, `ThemeAdminController`) referenced them. None of the
  nine reference `AdminUser` or anything Auth-specific; they are pure,
  generic "compose a form from sections" data and logic, so Core is their
  correct home on architectural merit, not just convenience.

## What was deliberately NOT done, and why

Two classes were **not** relocated: `AdminFormConfigAggregator` and
`AdminConfigLayeredFileLoader`. `zoosper-page`'s `composer.json` therefore
still requires `zoosper/admin`.

Unlike the nine classes above, these two are protected by a substantial,
deliberately-built safety net from the earlier Phase 1.40 config-layering
closure work:

- **3 Pest tests** in `app/zoosper-core/tests/Unit/Config/` that hardcode the
  exact class names (`Zoosper\Admin\Form\AdminFormConfigAggregator`,
  `Zoosper\Admin\Form\AdminConfigLayeredFileLoader`), an exact file path
  (`app/zoosper-admin/src/Form/AdminFormConfigAggregator.php`), and specific
  source-code string markers
  (`PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED`,
  `loadLayeredAdminFormConfigFile`).
- **6 `tools/*.php` scripts** performing discovery, functional-parity
  readiness auditing, wiring auditing, closure auditing, patch-application,
  and runtime-bridge proof — all likewise hardcoding the same class names,
  path, and markers.
- **2 `docs/development/*.md` files** (`admin-config-layered-runtime-bridge.md`,
  `admin-form-config-aggregator-layered-wiring.md`) documenting the original
  Phase 1.40n–r work that produced this exact pairing of classes.

Relocating these two classes would mean carefully rewriting all of the above
— 11+ files' worth of hardcoded namespace/path/string assertions — to
correctly track a new location, for two classes whose remaining benefit is
small: `zoosper-page` already gets the large majority of its architectural
decoupling from the nine classes already moved. The cost of unwinding a
mature, deliberately-built audit trail was judged disproportionate to the
value of removing the *last* dependency edge for one module.

There is also a legitimate architectural argument for leaving these two
where they are: `AdminFormConfigAggregator` aggregates configuration that
admin UI screens consume (it walks every module's `config/admin_forms.php`
and merges root overrides) — this is genuinely admin-runtime
configuration-loading machinery, not generic composition logic like the nine
classes that moved. Its original placement in `zoosper-admin` can be
defended on merit, not just history.

**Practical consequence**: `zoosper-page` cannot currently be installed
without `zoosper-admin` also being present. If a genuinely headless/API-only
Zoosper install (with zero admin UI) becomes a firm near-term goal, this
would need to be revisited — at that point, the cost of carefully migrating
the 11+ protective files becomes worth paying. Until then, this is treated
as a closed decision, not an open TODO.

## Verification

Each of the three modules has its own regression test proving the intended
end state:

```bash
# Confirms zero Zoosper\Admin\ imports remain, and composer.json no longer
# requires zoosper/admin.
zcomposer test -- --filter="zoosper-two-factor/src"
zcomposer test -- --filter="two-factor composer.json"

zcomposer test -- --filter="zoosper-media/src"
zcomposer test -- --filter="media composer.json"

# Page: confirms the 9 relocated classes exist under Zoosper\Core\Form, and
# that all four form section providers implement the relocated interface.
# Does NOT assert zero Zoosper\Admin\ imports for page (see above).
zcomposer test -- --filter="relocated admin-form classes exist under"
zcomposer test -- --filter="four page form section providers implement"

zcomposer test
```

Full suite: 747 tests passing as of this phase's completion.
