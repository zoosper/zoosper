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
