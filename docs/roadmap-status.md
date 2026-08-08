# Roadmap status

## Phase 9FLA-L: Media/Page closure and dependency honesty

- Media concrete Admin layout/view decoupling is complete and guarded.
- Page Admin layout/view construction uses Auth-owned interfaces and is guarded.
- Page structured `block_json` rendering with saved-HTML fallback is implemented and behaviourally tested.
- Managed Editor.js image sanitisation is composed from Media; Page now declares that runtime dependency explicitly.
- Active follow-up work is limited to remaining Page-specific concrete Admin editor/form/grid dependencies and further visible Page workspace improvements.

## Phase 9FM route-parameter cutover

Page edit, preview, publish and unpublish actions now expose constrained parameterised routes and resolve the Page identifier from immutable request route parameters. Existing query-string routes remain temporarily available for backwards compatibility, while newly generated Grid and edit-form links use canonical path URLs.

## Phase 9FN save and publication closure

Page create/update normalisation, extensible form processing, entity-save lifecycle execution and persistence now run through a Page-owned save coordinator. Single-Page publish/unpublish mutations and events run through a Page-owned publication coordinator. The Admin controller remains responsible for HTTP responses, flash presentation and redirects.

## Phase 9FO form and request-query closure

Page Admin form context, Site options, content-editor fallback, SEO fields, structured-content fields, CSRF token and extensible `page.form` section rendering now live in a Page-owned form renderer. Page Grid reads query state from the immutable Request query map rather than `$_GET`.

## Phase 9FP final Page Admin controller closure

Pages Grid screen composition and protected Grid mutations now run through a Page-owned Grid responder. Read-only Admin preview resolution now runs through a Page-owned preview responder while retaining the single `PageRenderer` path. `PageAdminController` is reduced to authentication, entity lookup, delegation, flash/redirect selection and HTTP response selection.

## Phase 9FQ dependency-honesty closure

Page package decoupling is closed at the honest compatibility boundary. The package keeps `zoosper/admin` while three Admin-owned shared contracts remain in active use. Runtime ownership guards prevent expansion beyond that allow-list. Contract relocation remains a separate cross-module compatibility project rather than unfinished Page controller work.

## Phase 9FR shared presentation-contract migration

The cross-module presentation-contract migration is complete: shared contracts moved to Core, all consumers and service registrations use the Core namespaces, obsolete Admin-owned contract files were retired, and Page/Settings removed their Admin Composer dependency.

## Phase 9FS dead-runtime and compatibility closure

Post-migration cleanup removed three unused Page Grid integration abstractions and their isolated tests. Core presentation contracts, Admin concrete implementations, Page Grid runtime composition, export, saved views, mutations, bulk actions and frontend boot remain regression-guarded.
## Phase 9FT Settings Admin runtime closure

Settings screen composition moved to `SettingsCatalogueResponder`; save and inherited-value restoration moved to `SettingsMutationCoordinator`; canonical Settings URLs are shared through `SettingsAdminUrls`. The controller retains only authentication, delegation and response return.

## Phase 9FU Settings presentation asset closure

Settings presentation was split into a semantic PHP template, a module-owned stylesheet and a deferred module-owned browser runtime. Existing workspace, saved-view, keyboard, print, accessibility and form contracts remain covered against the complete presentation bundle.

## Phase 9FV Settings presentation test consolidation

The post-asset-cutover Settings test suite now uses `settingsPresentationBundle()` as its single complete-presentation fixture. Existing accessibility, saved-view, search, print, keyboard and workspace assertions remain separate and unchanged.

## Phase 9FW Settings presentation model closure

`SettingsPresentationBuilder` now prepares stable view metadata while the template retains semantic iteration and markup.

## Phase 9FX Settings composition closure

`SettingsPresentationBuilder`, `SettingsScopeSelection` and `SettingsAdminUrls` now resolve through Settings-owned service registrations. Controller wiring composes responders and mutation coordinators from those registered collaborators without constructing the shared services directly.

## Phase 9FY Settings persistence contract closure

Scoped resolution, atomic section writes and reset-to-inherited operations now share `ScopedSettingStoreInterface`; `ScopeConfigSettingStore` contains the Core repository and transaction implementation details.

## Phase 9FZ Media upload runtime composition closure

The Media library and Editor.js upload paths now share the container-configured `MediaUploadService`; controller fallback construction was removed and cleanup wiring is no longer bypassed. Derivative execution remains separately gated on explicit processor and policy registration.

## Phase 9GA CSP and roadmap truth closure

The Admin user Reset 2FA control no longer uses inline JavaScript; a registered Auth asset preserves submit confirmation under an enforcing `script-src 'self'` policy. Roadmap summaries were reconciled with verified runtime and regression-test evidence for 2FA cleanup, module collision behaviour, Page/Settings decoupling, AssetResolver containment and Media upload composition.

## Phase 9GB CI quality-gate closure

CI enforcement now covers Composer metadata and audit, JavaScript syntax, the repository gate, full Pest and compile. Psalm runs visibly in advisory mode because the captured legacy baseline is non-zero; promotion to blocking is gated on reducing that baseline to zero.

## Phase 9GC Runtime boundary hardening

Module discovery now rejects cross-layer identity collisions with both paths and sources in the exception, preventing stale app/modules copies from silently masking vendor packages. Asset containment is behaviourally guarded against null bytes, encoded traversal and symlink escapes.

## Phase 9GD HTTP exception presentation closure

The Marko renderer now serves caught development HTTP exceptions as well as truly uncaught exceptions. `app.debug` from merged application configuration controls web detail exposure, while production and API responses remain safe and generic.

## Phase 9GE CLI recovery bootstrap closure

`help`, `list`, `compile` and `cache:clear` are regression-tested against an unreachable database. CLI uses the same `ApplicationConfigLoader` as HTTP, and module command composition now retains `PdoConnectionProvider` laziness through the service container rather than forcing PDO before command resolution.

## Phase 9GF Console kernel decomposition

Core now owns operational command objects for migrate, compile, cache clear and manifest diagnostics, plus reusable `ConsoleServiceFactory` and `ConsoleKernel` boundaries. The executable retains deployment and scaffolding orchestration as an explicit follow-up.
