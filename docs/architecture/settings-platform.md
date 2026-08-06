# Admin settings platform

Phase 9A1 establishes module-owned, typed and searchable settings metadata through the dedicated `zoosper-settings` package and module-owned `config/admin_settings.php` files. The catalogue is read-only and separate from runtime values. Static defaults continue under `config/settings/*.php`; scoped database overrides remain owned by `ScopeConfigRepository`.

The UI, source badges and persistence build on this contract in later Phase 9 increments. Secrets must be explicitly typed and flagged, and environment/project-owned values can be declared read-only.

## Ownership

`zoosper-core` owns low-level static and scoped configuration. `zoosper-settings` owns definitions, catalogue discovery, value/source orchestration, validation and settings-facing adapters. `zoosper-admin` remains the generic administrative UI shell.

## Phase 9A2 read-only admin adapter

`zoosper-settings` owns the protected `/admin/settings` route, controller, menu contribution and module view. The screen groups module-owned sections, exposes client-side catalogue search and displays metadata only. It contains no persistence form or secret value retrieval.

## Phase 9B effective values

The read-only catalogue resolves safe effective values from the existing `ConfigRepository`. Values present in merged project/runtime configuration are labelled `Project` and read-only; absent values use module defaults or `Unset`. Secret values are always redacted. Database and inherited provenance remain reserved for the scoped persistence adapter and are not guessed.

## Phase 9C1 scoped value resolution

The read-only catalogue now resolves the default scope through `ScopeConfigRepository::getWithSource()`. A value at the requested scope is labelled `Database`; a value resolved from a less-specific scope is labelled `Inherited`. With no scoped row, Phase 9B project/default/unset resolution remains authoritative. Secret scoped values are always redacted. Scope mutation remains absent.

## Phase 9C2 read-only scope selector

The Settings catalogue accepts `scope` and `scope_key` through `Request::query()`. Website, store and site keys are derived from, and validated against, real Site records. The selector builds a complete `ScopeContext`, preserves selection in the URL and remains read-only.

## Phase 9D1 typed save foundation

The Settings package now owns typed input normalisation and an atomic section writer. Only declared, non-secret, non-read-only definitions are accepted; unknown browser paths are ignored. Every editable field is validated before a transaction begins, preventing partial writes. This phase deliberately adds no POST route or editable UI; Phase 9D2 will connect the proven write service to CSRF-protected administrative forms.

## Phase 9D2 first editable setting

The catalogue now exposes a CSRF-protected POST flow for declared editable sections. Scope selection and section identity are revalidated on the server, project-controlled values remain locked, and writes use the Phase 9D1 atomic section writer. The first pilot is `settings.catalogue.show_paths`, a non-secret boolean setting. Unknown paths remain unable to reach persistence.

## Phase 9D3 pilot closure

The `settings.catalogue.show_paths` pilot now controls configuration-path visibility. Boolean fallback ordering is fixed so checked values persist as enabled. Administrators may clear a declared editable override at the selected scope through a CSRF-protected action, allowing inheritance to resume. Read-only, secret and undeclared paths cannot be cleared.

## Phase 9D4 form architecture and typed controls

Editable sections now use one valid section-level POST form. Clearing a scoped override uses a submit button with `formaction` rather than a nested form. The catalogue renders controls for all Phase 9D1 types: text, textarea, email, URL, integer, decimal, boolean, select and multiselect. Server-side normalisation and declared-option validation remain authoritative.

## Phase 9D5 runtime consistency closure

No broad cache invalidation is performed after a settings save or clear. Scoped values are read directly from `ScopeConfigRepository` on the redirected request, so committed database changes are immediately visible to the Settings resolver. The registered `Marko\Cache\Contracts\CacheInterface` is a general application cache and the current contracts expose no settings-specific key namespace or invalidation API. Flushing it would therefore be unrelated and overly broad.

Static project configuration remains represented by the immutable `ConfigRepository` and is never mutated by the Settings platform. Editable overrides remain isolated in `ScopeConfigRepository`. If a future runtime consumer introduces a settings-specific cache, that consumer must provide an explicit invalidation contract before Settings integrates with it.

## Phase 9E1 redacted audit logging

Successful section saves emit `settings.section.saved`; successful override clears emit `settings.override.cleared`. Audit events are recorded after persistence and contain actor identity, section ID, scope type/key and declared non-secret paths only. Values, full form payloads, CSRF tokens and secret paths are never included. Audit integration uses `AuditLoggerInterface` optionally and remains best-effort after the mutation has committed, matching established platform policy.

## Phase 9E2A category, section, group and field foundation

The Settings metadata hierarchy now supports category → module-owned section → group → field. `SettingsGroup` provides stable IDs, labels, descriptions, sort order and initial expansion state. `SettingsSection::settings` remains an intentionally flattened compatibility view so existing save, clear, audit, search and value-resolution services continue operating unchanged.

Modules may declare either `groups` or the legacy top-level `settings`, never both. Legacy metadata is normalised into an open `General` group. Duplicate paths across groups in one section fail fast. Administration now owns separate Interface and Routing groups. The visual category rail, section navigation and accessible accordion renderer build on this contract in the next UI phase.

## Phase 9E2B organised Settings workspace

The administrative workspace now renders the hierarchy as category navigation, module-owned section cards, accessible group accordions and typed fields. Only the active category is shown, limiting page length as modules grow. Search spans category, section, group, module and field metadata; matching groups expand automatically. Scope selection remains global, while save and clear operations remain section-scoped and retain existing CSRF, validation, inheritance and audit contracts.

## Phase 9E2C bulk external adoption and navigation

`zoosper-mail` is the first external module to contribute grouped Settings metadata. Email Delivery appears under Communication with Sender Identity, SMTP Connection and Authentication groups. All Mail fields remain read-only because the current runtime consumes immutable `ConfigRepository`; the SMTP password is marked secret and remains redacted. Scoped editing is deliberately deferred until Mail adopts a scoped runtime config interface and dedicated secret-write workflow.

The workspace now provides stable section anchors and an in-category section navigator when a category contains multiple sections. Search expands matching groups and highlights direct path matches, preserving manageable navigation as module contributions grow.

## Phase 9E2D bulk Theme adoption and workspace polish

`zoosper-theme` now contributes a Design → Theme Rendering section with Template Engine and Compiled Templates groups. These real project-consumed values remain read-only until Theme adopts scoped runtime resolution. Mail also exposes its real `mail.default` transport key.

Categories now follow the canonical product order General, Communication, Content, Design, Commerce, Security and Advanced rather than module discovery order. The active category is retained through both the URL fragment and browser-local workspace state. Header search now owns a responsive full-width column, preventing placeholder truncation on normal desktop layouts.

## Phase 9E2E bulk workspace navigation and accessibility

The Settings workspace now treats categories as an accessible tab set with linked tab/panel semantics and keyboard navigation. Active category and group expansion state are remembered locally. Stable category, section and group links can reveal and scroll to their targets. Descriptive category headings clarify ownership areas, search focuses direct matches, and the scope bar plus category rail remain available during long sections while retaining the existing narrow-screen fallback.

## Phase 9E2F bulk workspace operations

The organised workspace now provides client-side source views for all fields, database overrides, inherited values, project-controlled values and read-only definitions. Search and source filtering compose, hide empty groups and sections, activate the first matching category and publish a live field/section result count. Expand All and Collapse All operate on currently visible groups. The selected source view is retained locally. These controls do not alter server-side persistence, scope validation, inheritance, audit or secret-redaction contracts.

## Phase 9E2G bulk workspace productivity

The workspace now includes Reset View, `/` search focus and Escape-to-clear shortcuts. Reset View clears search, source filtering and remembered group expansion, restoring module-declared default-open groups. Category badges publish filtered section counts and hide zero-result counts. Direct section/group hashes are preserved while their category activates. The Default scope now hides and disables Scope value in server-rendered markup before JavaScript executes, preventing the brief empty selector state observed during page load.

## Phase 9E2H bulk section form state and truthful summaries

Editable section forms now track their initial serialised state. Save and Reset section actions remain disabled until a field changes; dirty sections receive a visible boundary and an `aria-live` status. Reset section restores the field controls to their initial rendered values. Browser navigation warns only when an unsaved form exists and no save submission is in progress.

Workspace result summaries now describe the active visible category rather than reporting totals across hidden category panels. Search and source filters still update all category badges, while the main summary reports visible fields and sections in the selected category.

## Phase 9E2I bulk field navigation and JavaScript validation

Every field now has a stable, value-free deep-link ID derived from its declared configuration path. Field headers expose a Link action, and when configuration paths are visible, a Copy path action writes only the declared path to the clipboard. Copy feedback uses an accessible live status. Direct field links reuse the existing hash-reveal flow, activating the containing category, opening the containing group and scrolling to the field.

The installer now extracts executable JavaScript blocks from the PHP view and validates them with `node --check` before running the PHP test suite. This guards browser syntax independently from PHP lint and prevents recurrence of the Phase 9E2G extra-closure defect.

## Phase 9E2J bulk density and print view

The workspace now offers Comfortable and Compact density modes. Compact mode reduces group and field spacing and suppresses secondary field descriptions and source explanations while preserving labels, paths, types, badges and effective values. Density is remembered locally and Reset View restores Comfortable mode.

A print action invokes the browser print flow. Print CSS removes interactive navigation, filters, field actions and save controls, reveals catalogue content and avoids splitting section cards where practical. Printing remains value-view only and never mutates configuration.

## Phase 9E2K aggressive print and workspace hardening

Print output now uses a Settings-owned header containing the selected scope and a print-time timestamp, hides known admin-shell regions, removes target/search outlines and interactive controls, expands every group before print, then restores the exact pre-print expansion state. Print page-break rules favour category and group headings with their following content while allowing long sections to flow across pages.

The workspace also adds Copy view link and Clear target actions. Copy view link copies the current scoped Settings URL and fragment without configuration values. Clear target removes only the field/section/group fragment and visual target state; it does not reset search, filters, density, scope or configuration.

## Phase 9E2L print layout and target-navigation cleanup

Print output now neutralises common admin-shell width, max-width, margin, padding and transform constraints so the Settings catalogue can use the printable page width. Categories after the first begin on a new page, while the final category, section, group and field explicitly avoid forced trailing page breaks. Fields remain indivisible where supported, and all catalogue containers use full print width.

Screen deep links now use scroll margins for sections, groups and fields so sticky scope and workspace controls do not obscure revealed targets.

## Phase 9E2M print value view and reusable runtime regression guard

Editable controls are now wrapped as screen-only controls with adjacent print-only value representations. Print output therefore shows a stable value view rather than checkboxes, selects, textareas or text inputs. Boolean values print as Enabled or Disabled; other editable types print their current effective value. Secret masking remains authoritative and no password control exists.

JavaScript extraction is now a reusable Settings test-support script rather than installer-local Python. Installers invoke the support script followed by `node --check`, making executable JavaScript validation repeatable in local and CI workflows.

## Phase 9E2N print index and active workspace state

Printed output now begins with a Settings-owned configuration catalogue index that identifies the selected scope and lists each category's section and field totals. The current value-free Settings URL is printed inside the document, allowing browser headers and footers to be disabled without losing source context. The screen-only live result count is suppressed in print because it describes the active browser category rather than the complete printed catalogue.

The screen workspace now exposes a concise active-state summary for search text, non-default source views and Compact density. This state contains navigation preferences only and never configuration values.

## Phase 9E2O module filter and editability view

The Settings workspace now derives a module selector from the module-owned sections already discovered by the catalogue. Module filtering composes with search, source views and density, updates category result counts and is remembered locally. No central module list or duplicated ownership registry is introduced.

The source-view selector now includes Editable. Editability is emitted as a value-free server-authoritative field attribute based on the same `$editable` collection used to render controls, so browser filtering cannot make a locked field writable. Reset View clears both module and source preferences.

## Phase 9E2P shareable workspace state

Copy view link now serialises only value-free workspace preferences into query parameters: search text, source view, module filter and density. Scope parameters and category/section/group/field fragments remain intact. Link state is validated against the currently available selector options before application; unsupported source, module or density values are ignored.

Reset View removes only the workspace query parameters while preserving scope and target fragments. No setting values, form payloads, CSRF tokens or secrets are encoded in a shareable URL.

## Phase 9E2Q workspace URL-state lifecycle

The redundant Apply link state toolbar action has been removed because valid URL state is applied automatically on load. Search, source view, module filter and density changes now synchronise the address bar through the existing allowlisted `buildWorkspaceUrl()` codec. Default choices are removed from the query, while scope parameters and target fragments remain intact.

Browser back/forward navigation reapplies validated URL state through the same option checks used during initial load. URL synchronisation contains navigation preferences only and never configuration or form values.

## Phase 9E2R search-result navigation

Search now presents all matches with a subtle background while reserving the strong outline for one current result. Previous match and Next match controls cycle through visible matches, activate the containing category, open the containing group and centre the field. Enter advances and Shift+Enter moves backwards while search has focus. Match position is announced through an `aria-live` region.

Search-result and current-result decoration is removed from print output. Clear target removes both target and current-match presentation without altering configuration or workspace filter values.

## Phase 9E2S compact workspace chrome

The Settings header, scope selector and workspace toolbar now use a denser layout so configuration content begins higher on the page. Source view, module, density and Reset remain visible as primary controls. Search-result navigation, link actions, print and expand/collapse operations move into an accessible More actions disclosure.

Empty live-status regions no longer reserve vertical space. On narrow screens the More actions panel and scope selector become static flow content to avoid viewport overflow. Configuration rendering, scope resolution, URL state, persistence and print behaviour remain unchanged.

### Phase 9E2S More actions positioning correction

The desktop More actions disclosure is now a positioned containing block, and its panel is anchored at `right: 0; top: 100%` relative to the disclosure. This prevents the action panel from drifting to the far-right viewport edge while preserving the static narrow-screen panel.

## Phase 9E2T contextual help and minimal chrome

Persistent explanatory text beneath the Settings title now lives in a native, keyboard-accessible `?` help disclosure. The disclosure contains section count, current scope, scope guidance, filtering guidance, protection notes and the More actions explanation. The normal page keeps only task controls and configuration content visible.

The Scope label is now visually hidden but remains available to assistive technology, and Reset to Default renders only outside Default scope. Active filter state and result count share one compact status line. Automatic link-state messages remain available as hidden live regions rather than consuming layout space.

## Phase 9E2U grouped More actions

The More actions disclosure now presents three labelled regions: Search results, Share and output, and Section display. Desktop uses a bounded three-column popover anchored to the disclosure; narrow screens use a single-column panel with section dividers. All existing action IDs and JavaScript bindings are retained exactly once.

## Phase 9E2V exclusive floating panels

The About Settings help disclosure and More actions disclosure are mutually exclusive. Opening either closes the other. Both close when the administrator clicks outside, presses Escape, changes category, or starts printing. The implementation retains native details/summary semantics and adds no new persisted workspace state.

## Phase 9E2W popover focus and keyboard polish

About Settings and More actions now expose deterministic summary/panel relationships through `aria-controls` and synchronised `aria-expanded`. Opening a disclosure moves focus to the first interactive item in that panel. Escape closes the active floating panel and restores focus to its summary trigger. Mutual exclusion, outside-click closing, category closing and print closing remain unchanged.

### Phase 9E2W search shortcut runtime correction v2

Enter and Shift+Enter navigation now uses one scoped handler for both the search input and the open More actions disclosure. The handler retains the established positive `event.key==='Enter'` source contract, requires visible matches, and does not intercept Enter outside those contexts.

## Phase 9E2X aggressive workspace hardening

Search navigation now supports Home and End for first/last visible matches, and the selected match updates the value-free field fragment for sharing. Enter and Shift+Enter remain scoped to search and the Search results action group. One-shot More actions operations close the disclosure after activation, while Previous/Next remain open for repeated navigation. Escape clears a non-empty search before blurring an empty search. Floating panels close before section submit/reset, and all primary workspace controls receive explicit focus-visible treatment.

## Phase 9E2Y saved workspace views

Administrators can save, apply and delete named workspace views from Share and output. A saved view contains only search text, source view, module filter and density through the existing `workspaceState()` allowlist. Saved views are browser-local, sorted by name, validated against currently available selector options and synchronised back into the shareable URL when applied. Configuration values, form data, scope credentials, CSRF tokens and secrets are never stored in a saved view.

## Phase 9E2Z saved-view hardening

Saved workspace views now use a versioned local-storage envelope, bounded collection size, bounded names and allowlisted state normalisation. Saving protects against accidental overwrite and reports the local capacity limit. Administrators can rename a selected view and copy a value-free JSON export of all personal views. Legacy unversioned objects remain readable and are normalised on access.

## Phase 9E3A saved-view import and portability

Administrators can import versioned Phase 9E2Z JSON or legacy unversioned saved-view objects. Import normalises every state through the existing value-free allowlist, bounds names and collection size, and offers merge or confirmed replacement when local views already exist. Invalid JSON is rejected without changing storage. Clear all views requires confirmation. Import, export and clear remain browser-local workspace operations and never contain configuration values, form payloads, scope credentials, CSRF tokens or secrets.

## Phase 9E3B-D saved-view completion bundle

### 9E3B file import
Saved views import through a hidden JSON file input rather than a prompt. Files above 256 KiB are rejected before reading; valid content continues through the Phase 9E3A parser, normaliser, bounds and merge/replace flow.

### 9E3C default workspace view
A selected saved view can be marked as the browser-local default. It applies only when the incoming URL contains no explicit `q`, `view`, `module` or `density` state. Rename, delete and clear-all keep the default pointer coherent.

### 9E3D cross-tab synchronisation
Storage events refresh the saved-view selector and announce saved-view/default changes made in another tab. Cross-tab sync updates workspace navigation controls only and never reads or writes configuration values.

## Phase 9E3E-G saved-view finalisation bundle

### 9E3E default visibility
The default saved view is marked in the selector and can be cleared explicitly. Set Default immediately refreshes the marker and Clear Default leaves all saved views intact.

### 9E3F duplication
A selected view can be duplicated under a bounded name. Capacity and overwrite rules match ordinary Save, and copied state passes through the same allowlist normaliser.

### 9E3G keyboard lifecycle
When the Saved Views selector owns focus, Enter applies the selected view, Delete invokes confirmed deletion, and Alt+D sets the selected view as default. These shortcuts operate only within the selector context.

## Phase 9E3H-J saved-view state lifecycle bundle

### 9E3H divergence state
The selected saved view reports Saved view active or Modified from saved view by comparing normalised, value-free workspace states.

### 9E3I update selected
Administrators can explicitly replace the selected saved view from the current workspace after confirmation. The write passes through the existing normaliser and leaves configuration values untouched.

### 9E3J restore selected
Restore Selected reapplies the selected view without changing its name, clears a stale field fragment, synchronises the allowlisted URL state and closes More actions.

## Phase 9E3K-M aggressive saved-view operations bundle

### 9E3K downloadable export
Saved views can be downloaded as `zoosper-settings-saved-views.json`. The temporary object URL is revoked after use; clipboard export remains available.

### 9E3L pinned views
Views can be pinned browser-locally and are sorted before unpinned views. Pin state follows rename, is removed on delete, and is cleared with Clear all.

### 9E3M dirty-form guard
Applying/restoring a view or importing navigation state warns when a Settings form is dirty because filters may hide unsaved controls. The guard never serialises configuration values and does not prevent an explicit confirmed operation.

## Phase 9E3N-P aggressive saved-view resilience bundle

### 9E3N storage recovery
Saved-view writes now report browser storage failures and return a success flag. Legacy/unversioned storage is rewritten through the current versioned normaliser during initialisation. Save, update and import do not report success after a failed local-storage write.

### 9E3O pinned visibility
Pinned views display a star marker in the selector without modifying their stored names. Pin changes made in another tab refresh the selector and status region.

### 9E3P page-leave dirty guard
The page uses the existing `data-dirty` form contract to request the browser's native leave-page confirmation when unsaved Settings changes exist. Native section reset explicitly clears the dirty marker.

## Phase 9E3Q-S saved-view closure bundle

### 9E3Q diagnostics
Administrators can copy a value-free diagnostic summary containing schema version, saved/pinned counts, default presence, selected view and selected-view state.

### 9E3R repair
Repair Views removes stale default and pin references, deduplicates pin metadata and rewrites the canonical versioned saved-view envelope.

### 9E3S personal workspace reset
Reset Personal Workspace clears saved views, default/pinned metadata and browser-local source/module/density preferences, then restores the Default workspace URL. It does not clear configuration values or database overrides. This closes the saved-view feature arc; subsequent phases should return to module adoption and runtime settings consumption.

## Phase 9F1G-I Mail Default-scope runtime adoption

### 9F1G runtime resolver
`SmtpConfig` now resolves database-backed scoped values before project configuration while preserving its one-argument constructor behaviour. Runtime scalar casting, timeout lower-bound handling and encryption normalisation remain at the Mail boundary. The SMTP password is resolved only through the runtime-only accessor and is never placed in `SettingValue`.

### 9F1H transport and diagnostics adoption
Mail service wiring supplies `ScopeConfigRepository` with `ScopeContext::default()`. `MailConfigurationInspector` now takes `mail.default` from `SmtpConfig`, removing its duplicate `ConfigRepository` read while continuing to report only `passwordConfigured`.

### 9F1I dependency and editability boundary
Mail continues to depend only on Core, avoiding a dependency on the Admin-oriented Settings package. Admin Mail fields remain read-only until a dedicated secret-write contract is implemented. A later request-context phase may provide site/store/website context to Mail; current service wiring deliberately adopts Default-scope overrides first.

## Phase 9F1J-L Mail runtime adoption closure

### 9F1J explicit-scope configuration factory
`SmtpConfigFactory` creates immutable runtime configuration for a supplied `ScopeContext` and exposes a convenience Default-scope method. Existing system-mail resolution remains Default-scoped, while future request-aware callers can opt into site/store/website resolution without changing `MailerInterface`.

### 9F1K scope-aware redacted diagnostics
`MailConfigurationInspectorFactory` creates diagnostics for explicit scopes through `SmtpConfigFactory`. SMTP password plaintext remains confined to the transport configuration accessor; diagnostics expose only `passwordConfigured`.

### 9F1L service and architecture consolidation
Mail now registers one shared `ScopeConfigRepository`; the Default `SmtpConfig` service is derived from `SmtpConfigFactory`. The transport interface remains unchanged and Mail still depends only on Core. This closes Phase 9F1 runtime adoption. Request-context selection is an optional future caller concern rather than a transport-contract change.

## Phase 9F2E-H Theme runtime adoption

### 9F2E runtime configuration
`TemplateRuntimeConfig` resolves `template.engine` and `template.template_cache_path` with Default-scope database precedence over project configuration. It normalises the supported engine allowlist and converts the cache path to an application-root absolute path.

### 9F2F engine-priority integration
`TemplateEngineRegistry` accepts an optional extension priority while retaining variadic pluggable engines and extension-based dispatch. The selected engine controls extensionless template candidate order; explicit file extensions continue to select their registered engine.

### 9F2G service adoption
Theme services share Core's `ScopeConfigRepository`, construct Latte with the runtime cache directory, bind `TemplateEngineInterface` to the selected built-in engine, and register both built-in engines. Default-scope wiring is deliberate; request-aware engine selection is not injected into `TemplateRenderer`.

### 9F2H package and safety boundary
Theme remains dependent on Core and Errors, not the Admin-oriented Settings package. Both Admin fields remain read-only. `THEME_CODE` continues to select a theme and is intentionally separate from template-engine selection.

## Phase 9F2I-L Theme integration closure

### 9F2I homepage shell alignment
The canonical homepage layout now applies the existing `page-shell` class to `site-main`. This aligns generated markup with the default stylesheet's centred width, padding, border and radius contract without duplicating CSS selectors.

### 9F2J service integration guards
Regression coverage locks runtime cache-path use, selected built-in engine binding, configured extension priority, and the shared registry used by both frontend and Admin renderers.

### 9F2K public compatibility guards
The registry's original variadic constructor remains a permanent compatibility boundary. Priority remains additive through `prioritise()`; Theme stays independent of `zoosper/settings`, and `THEME_CODE` remains theme selection rather than engine selection.

### 9F2L closure
Phase 9F2 is complete. Both Theme-owned settings have runtime consumers, extension-based pluggability remains intact, and homepage markup is aligned with its existing visual shell contract. Further Theme work should be feature-driven rather than additional runtime-setting abstraction.

## Phase 9F3E-H Admin content-editor runtime adoption

### 9F3E runtime selection configuration
`ContentEditorRuntimeConfig` resolves `editor.default_editor` and `editor.fallback_editor` with Default-scope database precedence over project configuration. Blank values fall back to `editorjs` and `textarea`; non-empty third-party editor codes are preserved.

### 9F3F registry compatibility
The public variadic `ContentEditorRegistry` constructor and additive `register()` API remain unchanged. Later registrations continue replacing an existing code, and selection remains the registry's responsibility.

### 9F3G service adoption
Admin services construct the selected `ContentEditorInterface` from `ContentEditorRuntimeConfig`. Editor.js retains its textarea submission fallback, optional Media image-tool integration and CSRF injection.

### 9F3H catalogue and package boundary
Admin contributes read-only built-in editor choices to the Settings catalogue while remaining independent of `zoosper/settings`. Custom module editor codes remain configurable through project/runtime configuration and service registration.

## Phase 9F3I-L Admin content-editor runtime closure

### 9F3I explicit-scope configuration factory
`ContentEditorRuntimeConfigFactory` creates immutable editor selection for a supplied `ScopeContext` and provides a Default-scope convenience method. Existing Admin service wiring derives its singleton runtime configuration through this factory.

### 9F3J behavioural selection coverage
Regression tests exercise the actual registry and editor adapters: Editor.js retains its hidden structured document and submitted textarea fallback, textarea remains the safe built-in selection, and a module-registered custom editor code remains selectable.

### 9F3K integration boundaries
Page continues to depend only on `ContentEditorInterface`. Media integration remains optional through nullable Editor.js image-tool configuration and CSRF collaborators; editor selection does not add a Page-to-Media or Admin-to-Settings dependency.

### 9F3L closure
Phase 9F3 is complete. Editor settings have scoped runtime consumers, explicit-scope construction is available, public registry compatibility is preserved, and behavioural integration is covered. Further editor work should be feature-driven, including eventual controlled editability and browser-level Editor.js validation.
