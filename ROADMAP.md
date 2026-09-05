# Zoosper CMS — Master Roadmap

**Last updated:** 2026-09-05 (Sydney)

## Current continuity status

- Latest immutable release: `v0.3.0-alpha.5`.
- Current development line: `0.3.1-alpha.1-dev`.
- **[x] GitHub CI MySQL Migration Failure Resolution:** Fixed `composer migrate` failure (`SQLSTATE[HY000]: General error: 1824 Failed to open the referenced table 'media_assets'`) by using `SchemaInspector` in `202608310001_create_media_queue_table.php` to verify table presence before issuing `CREATE TABLE ... FOREIGN KEY (asset_id) REFERENCES media_assets(id)`, allowing fresh database migrations on MySQL and SQLite to execute safely and defer table creation to the declarative schema engine.
- **[x] Secret Generation Hardening:** Added explicit `0600` file permissions to `GenerateSecretsCommand::writeToEnvFile()` after writing updated `.env` secrets.
- **[x] Phase 10BN:** generic pagination ownership moved from Core to the
  `zoosper/pagination` library. Zoosper's page parsing, default page size `20`,
  maximum page size `100`, and maximum page `100_000` remain stable; verified
  `marko/pagination` `0.8.5` is used only behind the Zoosper boundary.
- Completed API arc: 10AK, 10AL, 10AM-A, 10AM-B, 10AM-C, 10AN-A, 10AN-B/C and 10AO.
- **[x] Phase 10AP-A:** Media list, detail, and derivative reads use the feature-owned stateless `media:read` PAT boundary; collection reads now provide bounded pagination, allow-listed filters/sorting, deterministic metadata, and no private storage paths.
- **[x] Phase 10AP-B:** Canonical multipart upload uses the feature-owned stateless `media:upload` PAT boundary plus token-owner `media.manage`, reads files only from the immutable request boundary, delegates to the shared canonical storage/derivative/cleanup pipeline, returns HTTP `201`, and exposes no private paths or token secrets.
- **[x] Phase 10AP-C:** Media archive/restore and archived-first permanent deletion now share mandatory reference and derivative boundaries, fail closed on incomplete Page reference storage, preserve transactional metadata removal and conservative original/derivative cleanup, and provide Admin/API blocker feedback.
- **[x] Phase 10AQ:** module-discovery status now matches the Phase 9GC fail-closed implementation; stale silent-override claims and the obsolete false-signal override test were removed while the dedicated same-layer and cross-layer contracts remain authoritative.
- **Phase 10AS-H completed in source, browser-accepted, and pushed:** the Admin now has a permission-aware Dashboard, fluid light/dark shell and shared components, package-owned responsive Grid workflows, screen-scoped assets, theme-coherent feature surfaces, a sidebar-owned collapse control, semantic destination icons, and non-interactive navigation groups. Final accepted source is `364414a4878cde36fd89de8583326e4d1ff1f625`, verified by `1,550` tests with `11,157` assertions and a `3`-check standard quality gate with `0` errors and `0` warnings. This phase was not deployed.
- **[x] Phase 10AR:** current-source review disproved the stale historical allegations and corrected the confirmed environment-precedence defect. Process-manager/container values now remain authoritative over `.env`; focused verification passed `26` tests / `76` assertions, the full suite passed `1,557` tests / `11,175` assertions, the strict quality gate passed with `0` findings, and browser plus production-safe console acceptance passed.
- **[x] Phase 10AU:** implemented aggregated discovery manifestation. `ModuleRegistry` and `Module` now include a `discovery` map tracking configuration files (services, routes, etc.) across modules. `ModuleManifestCompiler` caches this map in `var/cache/modules.php`, eliminating hundreds of redundant `is_file()` and `glob()` calls during production boot. Loaders for services, routes, commands, events, and admin UI now consume this map.
- **[x] Phase 10AV:** graduated Content Security Policy (CSP) from report-only to full enforcement. Default configured to `report_only => false` in `config/security.php` and `.env.example`.
- **[x] Phase 10AW:** completed Admin Grid & Form kernel consolidation. Migrated `UserAdminController`, `RoleAdminController`, and `SiteAdminController` to the unified `AdminFormRenderer` with support for sections and Danger Zone deletions.
- **[x] Phase 10AX:** migrated `PageAdminController` to the unified `AdminFormRenderer`, enhancing the renderer to support `checkbox` and `html` blocks (Editor.js). Verified with `AdminPageFormAcceptanceTest`.
- **[x] Phase 10AY:** implemented media derivative offloading. Introduced `media_processing_queue`, `QueuedMediaProcessor`, and the `media:process-queue` worker command. Added pre-decode resource limits to `GdMediaProcessor`.
- **[x] Phase 10AZ:** built the Module Lifecycle kernel. Added database-backed module registry with `module:install`, `module:uninstall`, `module:enable`, and `module:disable` commands that dynamically filter the compiled module manifest.
- **[x] 2026-09-01 Re-Audit & 2026-09-05 Technical Review reconciliation:** Confirmed resolution of CRIT-01, CRIT-02, CRIT-03, HIGH-01, HIGH-05, and LOW-02 against source. Reconciled open status of HIGH-02 (CI MySQL execution step, verified with fix for fresh MySQL migration foreign key ordering) and updated launch-readiness backlog: referential integrity / foreign-key reconciliation (33 declarative FKs delivered), Psalm blocking CI gate (full-scope gate delivered), automated secret generation & boot validation (`security:generate-secrets` with 0600 file permissions delivered), absolute session lifetime controls (delivered), unauthenticated asset pipeline security verification (delivered), and release checklist expansion.

---

> **Single source of truth for high-level feature status.**
> High-level only — one line per capability, not per micro-phase. Detailed phase
> notes live under `docs/`. Update this file once a day at wrap-up: tick completed
> items, add newly planned ones, move things between sections.
>
> **CONTINUITY NOTE:** this file is the recovery mechanism if a chat session is
> ever lost. It has been verified to work: a fresh conversation can retrieve
> this file directly from OneDrive/SharePoint and reconstruct full project
> state without any chat history. Keep it genuinely current, not just at
> wrap-up — the more accurate this file is, the less any single conversation
> thread actually matters.

**Framework baseline:** PHP 8.5 · Pest/PHPUnit · Psalm · Latte · **Marko packages
(real, adopted usage — see §14) via zoosper/errors and zoosper/core**

Legend: `[x]` done & deployed · `[~]` in progress / partial · `[ ]` planned
`[R]` reported by external reviewer (Fable pass #1, 2026-07-29), verification status noted inline
`[R2]` reported by external reviewer (Fable pass #2, 2026-07-30), verification status noted inline
`[FIXED]` confirmed fixed and deployed this session

---

## 0. TOP PRIORITY — next phase

**`0.3.1-alpha.1-dev` is open.** The immutable annotated `v0.3.0-alpha.5` tag targets release commit `2ffd78032895c79bb148bd1df11c4c5f53e0d704`. Continue the roadmap from this new minor public-alpha development baseline without changing the released alpha.5 source.

**Planned Admin follow-ups for the current development line:**

- [x] The Admin-owned, module-discovered contributor contract now replaces Dashboard navigation repetition with dynamic widgets through the focused `zoosper/admin-dashboard` contracts package. Auth contributes an active-user metric from its own repository; Admin permission-filters before service resolution, isolates contributor failures, renders escaped responsive cards, and retains no concrete feature-module dependency. Admin-owned per-user preferences now add permission-safe show/hide, persisted order, reset-to-default, accessible keyboard movement, and CSP-safe drag enhancement. Auth-owned assigned-role defaults add `role.manage`-protected administration, audited changes, deterministic multi-role visible-union merging, and cascade cleanup without weakening permission-before-resolution.
- [x] Admin colour palettes are module-discovered from validated declarative manifests, rendered through an accessible server-owned selector, persisted by the single CSP-safe shell runtime, and mapped to compatible light/dark base modes. The built-in Ocean palette proves extensibility beyond Light and Dark while external palette CSS remains module-owned through `config/admin_assets.php`. Authoritative source verification and browser acceptance cover persistence, responsive and keyboard behaviour, representative feature surfaces, CSP, console cleanliness, and storage-disabled fallback.
- [x] Corrected non-persisting Grid column preferences as a behavioural workstream separate from presentation. The completed implementation persists normalised visibility and order together, reads legacy visibility-only rows without a schema migration, preserves query/bookmark precedence, and synchronises live column controls into CSRF-bearing POST forms. Authoritative Vagrant verification and browser acceptance passed for Pages and Store Orders, including direct Save columns, reload persistence, reset, and saved-view capture.
- [x] Reconciled the approved Fable Admin design language through the semantic foundation and a browser-accepted compact shell/sidebar, secure account popover, Dashboard hierarchy/customisation surface, and package-owned Grid hierarchy. The implementation excludes prototype-only navigation counts, fabricated data, hardcoded routes, and inline behaviour while preserving ownership and persistence contracts.
- [x] Extended the Fable workspace migration through Settings, role administration, Auth-owned Admin-user forms, Personal Access Tokens, and Permission Explorer while retaining feature ownership, source/public asset parity, responsive themes, CSP-safe enhancement, POST/CSRF mutations, role-assignment authority, PAT owner scoping, and one-time secret handling.
- [x] Multi-layer navigation discovery structures declarative module-owned hierarchy without introducing non-interactive click targets or horizontal overflow. Pre-render permission filtering enforces branch access, semantic icons and group headings preserve accessibility, and screen-scoped shell CSS/JS synchronises collapsed sidebar and active parent branch states without sidebar numeric badges.
- [x] Auth-owned assigned-user search, selection retention, and role-assignment discovery are implemented with parameterised lookups, deduplicated multi-selection preservation, progressive enhancement search filtering, and accessible selection counters without degrading large user datasets or bypassing permission boundaries.
- [x] **Real-Time Global Announcement Modal (future global-notifications workstream).** Extracted into its own dedicated module `zoosper/global-announcements` (`app/zoosper-global-announcements`). Super Admins have a Settings surface (`/admin/announcements`) to draft, publish, unpublish, and archive Global Announcements. Active authenticated users receive real-time updates via background polling and asynchronous acknowledgment, while offline users receive mandatory one-time modal delivery reconciled upon their next login. Acknowledgment records persist authoritatively by announcement and user in `admin_announcement_acknowledgments` with duplicate-safe idempotency, CSRF protection, and audit logging. Admin layout consumption is fully decoupled via `AdminAnnouncementProviderInterface`.
- [x] **Decoupled Content Editor Module (`zoosper/editor` in `app/zoosper-editor`).** Extracted `ContentEditorRegistry`, `EditorJsContentEditor`, `TextareaContentEditor`, and scoped `ContentEditorRuntimeConfig` out of `zoosper-admin` into a dedicated internal path module with standalone service registration, asset declarations (`zoosper-admin-editor-style`, `zoosper-admin-editorjs-bundle`, `zoosper-admin-editor-script`), and backwards-compatible class aliases.

**Pre-Launch Security & Architecture Teardown Findings (2026-08-31 Audit & 2026-09-01 Re-Audit):**

- [x] **[CRIT-01] Fail-closed HTML sanitization in Page save coordinator & input.** Confirmed resolved in source: `PageSaveCoordinator` and `PageSaveInput` require `HtmlSanitizerInterface` as a non-nullable constructor dependency and throw (fail closed) if unresolvable, eliminating the silent raw-input fallback that risks stored XSS under `|noescape` template rendering.
- [x] **[CRIT-02] 2FA encryption key placeholder blocklist, rotation & boot assertion.** Confirmed resolved in source: Insecure `APP_KEY` fallback for `TWO_FACTOR_ENCRYPTION_KEY` in `config/two_factor.php` is eliminated, placeholder blocklist (`change-me`, `change-me-before-production`, `secret`, `changeme`, `placeholder`) is enforced at service construction and boot assertion, and multi-key rotation (`previous_encryption_keys`) is verified with behavioral data-provider tests.
- [x] **[CRIT-03] `APP_DEBUG` default `false`, boot unification & environment assertion.** Confirmed resolved in source: Default is set to `'debug' => false` in `config/app.php`, and `APP_DEBUG=false` is enforced in `ProductionSecurityPolicy::assertEnvironment()`.
- [x] **[HIGH-01] Database production driver policy enforcement.** Confirmed resolved in source: Enforced real driver policy for `config/database_policy.php` (`DATABASE_ENFORCE_MYSQL_PRODUCTION`, `DATABASE_PRODUCTION_DRIVER`) at both `ConnectionFactory` and `ProductionSecurityPolicy` to forbid SQLite in production and staging environments.
- [x] **[HIGH-02] CI MySQL integration testing.** MySQL 8.0 service container and `pdo_mysql` extension added to `.github/workflows/quality-gate.yml` along with explicit step-level Pest execution targeting MySQL in CI alongside SQLite.
- [~] **[HIGH-03] Unified admin authentication rate limiting.** Reconciled `RateLimitReportOnlyAdminMiddleware` contract documentation to match active HTTP 429 enforcement under `RATE_LIMIT_MODE=enforce` and added runtime salt assertion; transport separation between LoginController and RateLimitMiddleware tracked as future architecture refinement.
- [x] **[HIGH-04] Admin user locale persistence regression test net.** Implemented dedicated behavioral regression test suite `AdminUserLocaleLifecycleRegressionTest` covering admin user locale normalization, repository persistence, model hydration, and session propagation.
- [x] **[HIGH-05] Consolidate environment reader closures across 14 config files.** Confirmed resolved in source: Consolidated local `$env` closures across all root `config/*.php` and module config files to call the global canonical `env()` helper.
- [~] **[MED-01] Static analysis coverage & CI gate.** Scope expanded to 15 first-party packages with baseline in `psalm-baseline.xml`; non-blocking `continue-on-error` in CI and `zoosper-session` inclusion tracked for zero-baseline transition.
- [~] **[MED-02] Dual templating / Latte role views.** Latte templates adopted for role management views; conditional raw-PHP fallback with `extract()` in `RoleAdminController` noted for future cleanup.
- [x] **[MED-03] Behavioral test assertion coverage.** Substantially resolved: added data-provider behavioral tests verifying construction failure for placeholder/empty keys in `SecretProtectorKeyEnforcementTest`.
- [x] **[LOW-02] Stale documentation cleanup.** Confirmed resolved: obsolete `autoload.php` comments and duplicate `SECURITY.md` sections removed.
- [x] **[LOW-03] Module registry & DB lifecycle.** Reframed: database-backed `ModuleRegistry`, `ModuleRepository`, and `module:*` CLI lifecycle management implemented across `app/*` and `packages/*`.
- [x] **[LOW-04] ApplicationFactory debug-flag computation unification.** Unified early error-handler `APP_DEBUG` check and configuration-driven `$config->get('app.debug')` check in `ApplicationFactory.php` to use canonical `env('APP_DEBUG', false)`.

**Launch-Readiness & Technical Audit Actionable Remediation Backlog (2026-09-01 Review & 2026-09-05 Update):**

- [x] **Complete referential integrity & foreign-key reconciliation:** Closed the first-party referential-integrity inventory with 33 declarative foreign keys, fresh SQLite parity, and fail-closed release diagnostics for additions, mismatches, and rebuild requirements (shipped in `v0.3.0-alpha.5`).
- [x] **Make Psalm static analysis a hard CI gate:** Made Psalm a blocking full-scope CI gate across all first-party modules and packages in `v0.3.0-alpha.5`; baseline reduction and zero-baseline transition ongoing.
- [x] **Automated secret generation and mandatory production boot validation:** Provided `security:generate-secrets` command (`bin/zoosper security:generate-secrets --write`), enforced `0600` file permissions on generated `.env`, and implemented fail-closed production boot checks for required keys/salts in `ProductionSecurityPolicy`.
- [x] **Absolute session lifetime and concurrent session limits:** Added `ADMIN_SESSION_ABSOLUTE_LIFETIME` controls and session expiration checks in Admin authentication lifecycle (`v0.3.0-alpha.5`).
- [x] **Unauthenticated asset pipeline hardening:** Expanded adversarial path-normalization and allow-list regression coverage for `/asset/{module}/{path}` (`v0.3.0-alpha.5`).
- [x] **Convert security-fix doc comments into permanent behavioral regression tests:** Completed across sanitizer, 2FA key blocklist/rotation, debug defaults, driver policies, and rate limiting.
- [x] **Expanded release checklist execution:** Verified release gate covering FK integrity status, Psalm blocking gate, secret presence, CSP enforcement, media queue health, asset pipeline adversarial verification, and dual-engine SQLite/MySQL CI.
- [x] **Resolve GitHub CI MySQL migration failure:** Hardened `202608310001_create_media_queue_table.php` with `SchemaInspector` table existence checks before creating foreign key constraints against `media_assets`, ensuring `composer migrate` runs cleanly on fresh MySQL databases.

The following earlier top-priority findings are retained as completed history:

**Both items from the 2026-07-29 top-priority list are now confirmed and
fixed:**

- **[FIXED] Privilege escalation via OR-permission routes.** Confirmed real:
  `/admin/users/edit` is gated by `['role.manage', 'user.manage']` (either
  sufficient), and `UserAdminController::update()`/`create()` previously
  wrote submitted `role_ids` unconditionally regardless of which permission
  the actor actually held. Fixed: role assignment now requires
  `role.manage` specifically. `update()` preserves existing roles if the
  actor lacks it; `create()` fails closed with a clear error. Verified with
  a test that reproduces the actual exploit path and confirms it's blocked,
  plus confirms a legitimate `role.manage` admin's workflow is unaffected.
  `RoleAdminController`'s single-permission gate (no OR) was deliberately
  left alone — architecturally different, not the same bug — flagged as a
  separate product decision if you ever want it tightened.
- **[FIXED] Rate-limit store race condition.** Confirmed real:
  `DatabaseRateLimitStore::recordAttempt()` did SELECT → branch →
  INSERT/UPDATE with no atomic upsert; two concurrent requests for the same
  identity+window could both miss the row and the second would throw an
  uncaught `PDOException` → 500. Fixed with a single atomic upsert
  statement per driver (MySQL `ON DUPLICATE KEY UPDATE`, SQLite
  `ON CONFLICT DO UPDATE`), the same pattern already used correctly
  elsewhere (`EntityExtensionValueRepository::upsert()`).

**Fixed, from a live production incident (2026-07-30):**

- **[FIXED] 2FA encryption key rotation caused a real admin lockout.**
  Confirmed via live `exception.log` + nginx access log: an admin who
  enrolled in 2FA before `TWO_FACTOR_ENCRYPTION_KEY` was set (or before it
  was later changed) became permanently unable to log in —
  `SecretProtector::reveal()` only ever tried a single key. Fixed with real
  key-rotation support (current key for encrypting; current + a list of
  previous keys tried for decrypting — the same pattern Laravel uses via
  `APP_PREVIOUS_KEYS`), plus opportunistic re-encryption on successful
  login so each admin's rotation window closes itself automatically.
  Recovered live via `TWO_FACTOR_PREVIOUS_ENCRYPTION_KEYS`. `APP_KEY` was
  separately confirmed to have zero other consumers in the codebase and is
  now safe to rotate independently at any time.

**Fixed, from a second Fable review pass (2026-07-30):**

- **[FIXED] `bin/zoosper deploy` migrated against a stale cached module
  list.** Confirmed real: once module-list compilation was added, a release
  that adds a new module could have its migrations silently skipped if an
  older compiled cache still existed on disk — regardless of `deploy`'s
  step order. Fixed at the root (not just by reordering steps, which would
  only protect the `deploy` command specifically): `Migrator` and
  `SchemaLoader` now both call `ModuleRegistry::discoverModulesLive()`
  instead of `enabledModules()`, so migrations always see live, current
  truth. Verified with a test that directly reproduces the exact scenario
  (compile a cache, add a module afterward, confirm its migration still
  runs).

**Still open, from the second Fable review pass:**

- **[FIXED, Phase 9GE] CLI recovery commands are database-independent.** `help`, `list`, `compile`, `cache:clear` and manifest inspection execute without resolving PDO. The shared `PdoConnectionProvider` remains lazy and module command services receive PDO through a lazy container factory.
- **[FIXED, Phase 9GE] CLI and HTTP share layered configuration.** Both boot paths use `ApplicationConfigLoader` with module defaults below root overrides; the remaining console discovery regression fixture was migrated off the obsolete root-only loader.
- **[RESOLVED] Redundant test/session and one-shot cleanup scripts retired** —
  `composer test` remains the canonical Pest entry point. The unused
  `bin/pest.sh`, interactive `collect-and-run.sh`, and completed one-shot
  cleanup scripts were removed in Phase 2C.

---

## 14. Marko Framework Adoption Strategy

The project's original architecture intent (see `README.md`: "...inspired
by ... Marko PHP module conventions") was to build as much of Zoosper as
possible on top of real Marko packages rather than reinventing them. That
intent was not consistently followed prior to 2026-07-30 — `marko/core`,
`marko/errors`, `marko/errors-simple` were installed in `vendor/` but
completely unused. The discipline going forward: **before writing any new
subsystem from scratch, check Marko's real package catalog first** (by
reading actual installed source / real docs — not guessing from package
names), and this is now an explicit project rule in `AGENTS.md`.

### Adopted (real, verified integration)

- **`marko/core` (`MarkoException`)** — `ZoosperException` now formally
  `extends MarkoException` (additive: `ZoosperException` was already a
  strict superset — `docsUrl`/`details` have no Marko equivalent).
- **`marko/errors` (`ErrorReport`, `Severity`)** — real error-reporting
  pipeline; `ZoosperException` is automatically recognised by
  `ErrorReport::fromThrowable()`'s own `instanceof MarkoException` check,
  with zero glue code.
- **`marko/errors-simple` (`TextFormatter`, `BasicHtmlFormatter`,
  `CodeSnippetExtractor`, `Environment`)** — real CLI/web exception display,
  wired via `Zoosper\Errors\ExceptionDisplayer`. Deliberately NOT a
  wholesale replacement of `ErrorHandler` with Marko's own
  `SimpleErrorHandler` — that class has no file-based logging at all;
  composing (log via existing `LocalLogger`, then display via Marko's
  formatters) preserves existing log output while adding real display.
- **`marko/cache` + `marko/cache-file` + `marko/cache-redis`** — a real,
  configurable (file or Redis, one config flag) cache backend, via
  `Zoosper\Core\Cache\CacheDriverFactory`. Both drivers installed and
  tested (the Redis test attempts a real connection and honestly skips —
  never falsely passes/fails — if Redis isn't reachable).
- **`marko/config` (`ConfigRepositoryInterface`)** — required transitively
  by `marko/cache`'s `CacheConfig`/`marko/encryption`'s `EncryptionConfig`.
  Satisfied via a new `Zoosper\Core\Config\MarkoConfigRepositoryAdapter`,
  wrapping Zoosper's own `ConfigRepository`. Deliberately kept *inside*
  `zoosper-core` rather than a new `zoosper/config` package: a separate
  package wrapping `zoosper-core`'s own `ConfigRepository` would create a
  circular dependency (`zoosper-core` needing the new package; the new
  package needing `zoosper-core`). See "Deferred" below for the real,
  larger extraction this points toward.
- **`marko/encryption` (`EncryptionConfig`)** — backs
  `Marko\Cache\Redis\Signer\CacheValueSigner`'s HMAC tamper-detection for
  Redis-cached values. Uses a dedicated `CACHE_ENCRYPTION_KEY` — never a
  reuse of `APP_KEY`/`TWO_FACTOR_ENCRYPTION_KEY`, matching the
  single-purpose-key discipline already applied to 2FA.

### New package extracted: `zoosper/errors`

First module extracted out of `zoosper-core` into its own standalone
package (`packages/zoosper-errors`), following the same path-repository
pattern already proven by `packages/zoosper-media`. Owns
`ZoosperException`, `SensitiveValueRedactor`, `ConsoleExceptionFormatter`,
`ExceptionDisplayer` (owns every direct `Marko\*` import — `zoosper-core`
itself has **zero** direct Marko dependency in its own `composer.json` for
error handling, only `zoosper/errors`, which transitively provides
`marko/core`+`marko/errors`+`marko/errors-simple`). Has its own
`.gitattributes` marking `tests/`/`phpunit.xml.dist` as `export-ignore`,
matching Marko's own real package convention (confirmed by reading
Marko's actual GitHub repos directly).

**`.gitattributes` timing, settled**: only added at the moment a module is
*actually extracted* into `packages/` as a real, separately-exportable
package. `app/zoosper-*` modules are path-repository entries within the
monorepo, not separately-exported packages — `export-ignore` would be
inert for them today (there is no "export" event happening). Add it when
each module gets extracted, not before.

- **[Done]** Extract logger (`marko/log` + `marko/log-file`) as a standalone package (`packages/zoosper-logger`) following the `zoosper/errors` template. `LogManager` and `LocalLogger` map onto Marko's daily-rotated file logging with structural redaction and multi-channel support.
- **[Done]** Extract Admin Form kernel as a standalone package (`packages/zoosper-admin-form`). Unified Form registry, definitions, and renderer are now fully decoupled from Core and available for cross-module consumption.

### Evaluated and explicitly deferred: `marko/database`, `marko/database-mysql`

Real, attribute-driven entity mapper (`#[Table]`/`#[Column]` classes,
auto-generated migrations, `QueryBuilderInterface`, `Repository` +
`EntityCollection`) — genuinely comparable in ambition to this project's
own `Schema/`+`Database/` namespaces. **Deliberately not adopted**: this
would mean rewriting the entire persistence layer (every module's
`config/db_schema.php`, `SchemaSqlBuilder`/`SchemaMigrator`/
`SchemaValidator`/`SchemaLoader`/`Migrator`/`ConnectionFactory`, and every
repository across every module) — touching literally everything hardened
this session (`EMULATE_PREPARES`, collation, `tableExists()`'s MySQL-
protocol fix, the compile step, the stale-cache fix). This is a dedicated,
separate planning conversation, not a quick extension. **Also flagged**: a
possible version mismatch between `marko/database-mysql` (requires
`marko/database: 0.6.1`) and `marko/database` itself (listed at `0.8.4`) —
needs verifying directly before trusting this stack for anything real; no
SQLite driver was found in the docs reviewed, which would be a real
concern given this project's dual-driver (SQLite test / MySQL prod)
testing approach.

**Root `composer.json` cleaned up accordingly (2026-07-31)**:
`marko/database`, `marko/database-mysql`, and `marko/framework` (a
metapackage that pulled in the above plus `marko/cli`/`marko/hashing`/
`marko/routing`/`marko/validation`, none of which anything actually used)
were all removed from the **root** `composer.json` — confirmed via a real
`composer update` that all 7 transitively-unused packages were cleanly
removed with zero breakage. Every Marko package Zoosper actually uses is
now declared as a **direct dependency of the specific module that uses
it** (`zoosper/errors` → `marko/core`+`marko/errors`+`marko/errors-simple`;
`zoosper/core` → `marko/cache`+`marko/cache-file`+`marko/cache-redis`+
`marko/config`+`marko/encryption`) — the root project itself now has zero
direct Marko dependencies, matching the same "depend on what you actually
use, at the module that actually uses it" discipline already applied
throughout this session.

### Adopted for read-replica support: `marko/database-readwrite` (queued, not yet built)

A small, genuinely additive decorator: routes writes to one primary
connection and reads to one or more replicas, wrapping *any* existing
`marko/database`-compatible connection. **Important, honestly-flagged
correction to earlier analysis**: this package requires a real
`Marko\Database\ConnectionInterface`/`TransactionInterface`-compatible
connection — i.e. it is NOT a generic wrapper around any PDO connection;
it depends on the `marko/database` connection abstraction specifically.
This means genuine read-replica support via this package is **not** fully
decoupled from the `marko/database` adoption question above the way
originally believed — needs the real `ConnectionInterface` source read
directly before designing anything further here. Queued, not started.

### Not yet researched — queued for the next planning pass

- **`marko/cli`** — relevant to the "every CLI command requires a live
  database" bug (§0 [R2]) and the broader `bin/zoosper` structure.

### Noted overlap, not being pursued now (bigger architectural decision)

- **`marko/authentication`** (`AuthManager`, `SessionGuard`, `TokenGuard`,
  `AuthenticatableInterface`) and **`marko/authorization`**
  (`GateInterface`, Policies, `#[Can]` attribute) and **`marko/admin-auth`**
  (`PermissionRegistry` with wildcard matching, `#[RequiresPermission]`) —
  conceptually parallel to this project's own `SessionGuard`, ACL tree, and
  manual permission-check `if` statements scattered through controllers.
  **Confirmed: Marko has no 2FA/TOTP/MFA package at all** — searched
  directly, including all four linked package docs plus a dedicated search
  for `marko/two-factor`/`totp`/`mfa`/`otp` — so `zoosper-two-factor` is
  genuinely original and correctly should NOT be replaced. The
  authentication/authorization overlap is a real, bigger decision
  (potentially shifting the whole permission-checking approach to
  attribute-based declarations) — worth a dedicated evaluation later, not
  rushed.

---

## 15. High-Traffic & Read-Replica Readiness

Planning assumption, stated explicitly by the project owner: this CMS may
need to support a high-traffic website and may require a database read
replica.

- **[FIXED, foundation] HTTP cache subsystem is no longer inert.**
  `CacheDriverFactory` (via `marko/cache` + file/Redis drivers) provides a
  real, working, configurable cache backend. On top of it,
  `Zoosper\Core\Routing\CachingFallbackHandler` provides a real, opt-in
  frontend page cache (disabled by default via `config/page_cache.php`) —
  decorates any `FallbackHandlerInterface`, fails open on every unsafe
  condition (non-GET, no `SiteContext`, non-200 response, cache backend
  errors), never breaks page rendering because of a caching problem. A
  confirmed, real incompatibility was found and fixed while building this:
  `CacheKeyBuilder`'s own key format (`:`-separated, `/`-permitting) is
  explicitly rejected by Marko's own cache-key validation — fixed by
  hashing the correctly-computed structured key into a guaranteed-safe
  format, verified directly against the real, installed `FileCacheDriver`
  (not just a fake). **Two honestly-stated, still-open limitations**: does
  not vary the cache key by query string (no generic raw-query-string
  accessor exists on `Request` yet — deliberately not reaching around that
  via `$_SERVER`, matching the `Request::form()` immutability discipline);
  assumes one theme per site (the decorator only has the `Request`, not
  the resolved `Site` model).
- **Read replica support**: see §14 — `marko/database-readwrite` requires
  a `marko/database`-compatible connection, so this is not as fully
  decoupled from the (deferred) `marko/database` adoption question as
  first believed. Needs its real `ConnectionInterface` contract read
  before any further design.
- **Extend the compile step beyond just the module list** (§1, §13.2) —
  currently only `ModuleRegistry`'s module list is cached; ~14 other
  per-request loops (routes, services, controllers, events, ACL,
  translations, grid columns, etc.) still re-scan every request. This
  matters far more at high traffic than at low.
- **Instance-level memoization risk under long-lived workers** (§4) —
  `SessionGuard::$cachedUser` and similar per-instance caches are safe
  under classic PHP-FPM (fresh process per request) but become a genuine
  cross-request session-confusion risk if high traffic ever motivates a
  move to Swoole/FrankenPHP-style long-lived workers. Needs an explicit
  "rebuild request-scoped services per request" story before any such move
  — not just the existing manual `clearCache()` escape hatch on one class.
- **Rate-limit operations at scale** — enforcing Admin-login throttling now
  exists and is mandatory in staging/production. Continue tuning bounded
  policies and observability from production evidence as traffic grows.
- **Every CLI command requiring a live DB** (§0 R2) and **CLI/HTTP config
  divergence** (§0 R2) become more painful operationally at higher
  deployment frequency/scale (cron jobs, scaling scripts, health checks).

---

## 1. Core Platform & Architecture

- [x] Modular package layout (`app/zoosper-*`, plus `packages/` for
  extracted standalone modules) with per-module `composer.json` + PSR-4
- [x] Service container + module-owned `config/services.php` providers
- [x] Module route loader (admin + API) & controller providers
- [x] Config aggregation from module `config/*` (HTTP path only — see §0 R2
  for the CLI divergence)
- [x] Declarative schema engine (validator, migrator, snapshot audit)
- [x] Driver-aware schema detection (MySQL + SQLite)
- [x] Entity save lifecycle + listener discovery
- [x] Entity extension (EAV) data persistence
- [x] Event/observer bus (module-discovered listeners)
- [x] Method plugin / interceptor system (report-only seam)
- [x] Core ⇄ feature decoupling (+ behavioural namespace-ban guard test,
  independently praised by reviewer passes as "legitimately well-built")
- [x] Site-lookup boundary: core-owned `SiteLookupInterface` + `NullSiteLookup`
- [x] Frontend fallback handler wiring (fatal fixed) + boot-and-serve test
- [x] All 12 first-party modules promoted to real Composer packages (Phase 1.40)
- [x] Module-owned migrations (Phase 1.40c)
- [x] Console/kernel decoupling — `admin:create`/`site:create`/`page:create`
  discovered per-module via `ModuleConsoleCommandLoader`
- [x] **Module manifest compile step** (`bin/zoosper compile`/`cache:clear`/
  `deploy`) — caches the module *list* to `var/cache/modules.php`, safe
  fail-back to live scan if missing/corrupt.
- [x] **[FIXED] Migrations always use live module discovery** — see §0,
  the stale-cache deploy bug.
- [x] **[FIXED] Real, configurable page cache foundation** — see §15.
- [x] Admin/module dependency decoupling: two-factor and media are complete; shared presentation contracts moved to Core in Phase 9FR, and Page and Settings no longer require `zoosper/admin`.
- [x] **[FIXED] Layered module discovery collision diagnostics.** `ModuleRegistry` scans four runtime patterns: `app/*/module.php`, `modules/*/module.php`, `modules/*/*/module.php`, and Composer packages under `vendor/*/*`. Same-layer duplicate identities and all app/modules/vendor cross-layer identity collisions now throw descriptive `DuplicateModuleException` failures; silent priority-based shadowing was removed in Phase 9GC.
- [x] **Declarative Schema Foreign Keys.** Typed foreign-key support in `SchemaForeignKey`, `SchemaSqlBuilder` (MySQL and SQLite constraint generation), `SchemaValidator` (cycle and dangling-reference validation), and declarative module schema manifests (`app/zoosper-global-announcements`, `packages/zoosper-media`, etc.).
- [x] Container autowiring (Phase 1.367). Reflection-based parameter resolution and circular dependency detection implemented in `ServiceContainer`.
- [x] Module lifecycle (install/enable/disable/uninstall)
- [ ] Composer packaging + 0.x tag + CHANGELOG + stability contract — every
  internal module dependency still uses unconstrained `*@dev`
- [x] Database production driver policy enforcement: check `config/database_policy.php` flags in `ConnectionFactory` / `ProductionSecurityPolicy` and reject invalid driver/environment pairings.
- [x] Consolidate 14 duplicated `$env` closures in `config/*.php` into global canonical `env()` helper.
- [x] Phase 1.373: Extend Module Manifest Compilation. Aggregated services and routes are compiled into `var/cache/` to eliminate per-request module iteration and filesystem overhead.
- [x] Document third-party extension architecture and role of `modules/` placeholder directory vs path-repository `app/` and standalone `packages/` (see `docs/modules.md`).

## 2. Sites, Pages & Content

- [x] Multi-site + site domains (store-view model) + admin CRUD
- [x] Pages CRUD (admin) + revisions
- [x] SEO metadata fields
- [x] Editor.js content model + JSON save pipeline
- [x] Block JSON → HTML rendering + HTML sanitization (HTMLPurifier)
- [x] Frontend page rendering via themes
- [x] `content_json` frontend rendering via `PageRenderer` (comprehensive Editor.js block types: paragraphs, headers, lists, images, quotes, delimiters, code, tables, raw)
- [x] Router path parameters
- [x] Consolidate `pages` table into declarative schema
- [x] Make `HtmlSanitizerInterface` a mandatory, non-nullable dependency of `PageSaveCoordinator` / `PageSaveInput` and fail closed (throw exception) instead of falling back to raw unsanitized input.
- [x] Reversible disable/restore and permanent deletion lifecycle architecture across Pages, Sites, Site Domains, Roles, Menus, and Media, with CSRF protection, permission gating, audit logging, and safe identity deactivation for Admin Users.

## 3. Themes & Templating

- [x] Latte + PHP template engine adapters
- [x] Theme repository + per-site theme selection + theme admin
- [x] Module/theme template overrides (path-safe) + layout update system
- [x] RoleAdmin → Latte cutover (users and roles both on Latte with auto-escaping)
- [x] Adopter theme override story documented end-to-end (see `docs/themes.md`)
- [x] **CSP reporting endpoint configuration & readiness.** Configurable `report_uri` supported in `SecurityHeaders` and policy composition; CSP ships in report-only mode by default for observational readiness.

## 4. Admin & Auth
- [~] **Unified admin grid workspace.** The Pages grid now supports configurable
  columns, filters, page size, CSV export, bookmark persistence, draggable
  ordering with locked ID/Actions anchors, and immediate live table reflection.
  `zoosper-admin-grid` now owns the canonical generic column runtime; the
  application bridge is parity-guarded compatibility wiring. Content-derived
  asset versions and JavaScript syntax checks are enforced. Server-rendered header keys are now explicit and positional inference is
  removed. Application compatibility assets are retired. Remaining: add DOM coverage
  before rolling the workspace out to every admin grid.

- [x] Admin authentication + session guard
- [x] Roles, permissions, ACL tree + admin users CRUD
- [x] CSRF + auth middleware pipeline (OR-permission semantics)
- [x] Audit log + login history
- [x] Admin navigation / dynamic menu
- [~] Admin form section + processor registries — only actually used by
  the Page form; every other admin form still uses the older,
  non-extensible `AdminFormDefinition`/`AdminFormField` pair
- [x] i18n / translations / admin locale preference
- [x] 2FA (TOTP) enrolment, reset, recovery-code generation
- [x] 2FA enforced at login (Phase 1.107), with recovery-code redemption
- [x] Login history recording bug fixed (Phase 1.113)
- [x] **[FIXED] 2FA encryption key rotation support** — see §0, the real
  production lockout incident.
- [x] **[RESOLVED] Parallel 2FA stack concern verified as already retired.** Regression coverage confirms the obsolete `TwoFactorSecretProtector`, `AdminTwoFactorRepository`, and British-spelling enrolment service are absent; the live Crypto/Totp/Recovery/Enrollment stack is the only implementation.
- [x] **[FIXED] `bin/zoosper key:generate` / insecure default key** —
  `config/two_factor.php`'s literal-default fallback removed entirely;
  `SecretProtector`'s service factory now fails loudly if no real key is
  configured (enforced at point of use, not in the eagerly-loaded config
  file, to avoid breaking unrelated boot paths).
- [x] Memoize `SessionGuard::user()` per request — memoized in-process with explicit reset/cache-clear API for worker runtimes.
- [x] Admin god-module split — Page/User/Role/Theme/Media/Token/Announcement admin controllers relocated to their respective feature modules.
- [x] Batch-load permissions in `AdminUserRepository` (fix N+1) — Phase 1.109
- [x] Pagination + retention for audit log & login history — `PruneLogsCommand` (`admin:logs:prune`) and persistent Grid workspaces.
- [x] Consolidate duplicated Grid Criteria/SqlBuilder/Workspace and Admin Form section/processor patterns into a single extensible, typed Grid & Form kernel.
- [x] Dedicated behavioral regression test suite for admin user locale persistence across hydration, update, and session state.
- [x] Formalise session security controls: explicit absolute session lifetimes (`ADMIN_SESSION_ABSOLUTE_LIFETIME`), idle timeout resetting on active navigation, password update invalidation (`SESSION_PASSWORD_HASH_KEY`), and SameSite/Secure cookie policy verification during bootstrap.
- [ ] Add covering indexes and EXPLAIN query plan checks for admin grid search queries.
- [x] **[RESOLVED] Two competing Grid systems consolidated** — unified on
  `GridDefinition`/`GridCriteria`/`GridColumnRegistry` and
  `GridCompactWorkspaceRenderer` across all admin screens.
- [x] **[FIXED] Grid "sortable" columns silently ignored.**
  `AuditLogRepository`/`LoginHistoryRepository` hardcoded `ORDER BY id
  <direction>`, never consulting `$criteria->sortBy`. Fixed with an
  explicit, safe allow-list mapping known sort keys to column expressions
  (not raw SQL interpolation) — zero behavior change today (only
  `created_at` is currently declared sortable), but now genuinely
  extensible for future sortable columns.

## 5. Security

- [x] Baseline security headers, secure session cookie defaults, CSP
  (report-only) + HSTS, constant-time authentication
- [x] Rate-limiting subsystem wired onto `/admin/login`, report-only mode
- [x] **[FIXED] `PDO::ATTR_EMULATE_PREPARES` never explicitly disabled** —
  now `false` for MySQL/MariaDB; real server-side prepared statements.
  (This surfaced a real, separate bug: `Migrator::tableExists()`'s
  `SHOW TABLES LIKE :table` doesn't support real bound parameters in
  MySQL's protocol — fixed by switching to `information_schema.TABLES`.)
- [x] **[FIXED] Rate-limit identity salt defaulted to empty string** — now
  configurable via `RATE_LIMIT_IDENTITY_SALT`, with enforcement at the
  point of actual use (only when rate limiting is explicitly enabled) so
  the default, disabled installation is unaffected.
- [x] **[FIXED] `HTML_SANITIZER_DRIVER=basic` had no production guard** —
  now requires an explicit, separate `HTML_SANITIZER_ALLOW_BASIC_DRIVER=true`
  confirmation; also fixed a confirmed, real `??`/`?:` operator-precedence
  bug in that same config file's `$env` closure.
- [x] Staging and production fail closed unless rate limiting is enabled in
  `enforce` mode with a strong identity salt; the Admin login middleware
  returns a generic HTTP `429` with `Retry-After` when the policy denies.
- [x] CSP reporting endpoint (`report_uri`) and environment toggles (`SECURITY_CSP_ENABLED`, `SECURITY_CSP_REPORT_ONLY`, `SECURITY_CSP_REPORT_URI`) wired in `config/security.php` and `.env.example`.
- [x] Password min-length/complexity + `password_needs_rehash()` upgrade path; `SessionGuard` password hash fingerprinting invalidates active sessions across devices upon password update or reset.
- [x] Staging and production fail closed when `SESSION_SECURE` is unset or false.
- [x] CSRF decision for stateful `/api/*` session routes — stateless Bearer PATs vs session CSRF model documented in `docs/api.md`.
- [x] Atomic admin writes (transaction-wrap user/role create+sync) — fixed
  in both `RoleRepository` and `AdminUserRepository`
- [x] **[FIXED] `Request::form()` read live `$_POST` directly**, breaking
  its own immutability contract (every other accessor is pure/constructor-
  injected). Now reads from an immutable, constructor-provided property,
  captured once in `fromGlobals()`. No backward-compat shim added (per
  explicit project decision — pre-launch, no external users).
- [x] Structural email-log body redaction; `entity_extension_values`
  write-time field validation; truncate `user_agent` in audit/login-history
- [x] **[FIXED] `bootstrap/autoload.php` — 4 confirmed bugs**: the dead
  fallback autoloader (only mapped 6 of 12+ namespaces, replaced with a
  clear fail-fast error), the `env()` `??`/`?:` operator-precedence bug,
  3 real `.env` parser bugs (inline comments, quote-stripping, `putenv()`
  consistency), and a missing `function_exists()` guard. The historical competing-loader claim is closed: architecture regressions prove
  those loaders are absent and `bootstrap/autoload.php` is the sole owner.
- [x] **[FIXED] PDO connected before the error handler registered** —
  `ApplicationFactory::create()` registers `ErrorHandler` before module discovery, config loading, and database connection.
- [x] **[FIXED] `MediaUploadServiceResult::$stored` typed `?object`**
  instead of the concrete `StoredMediaFile` — now properly typed, with an
  explicit runtime guard in `MediaEditorJsUploadController` that throws
  loudly (rather than silently degrading to an empty `publicPath`) if the
  now-impossible null-stored-but-successful state is ever reached.
- [x] LICENSE (MIT) + SECURITY.md added — closes a real repo-hygiene/legal
  ambiguity gap flagged by an external reviewer pass.
- [x] Remove `APP_KEY` fallback for 2FA encryption key, enforce placeholder blocklist (`change-me`, `change-me-before-production`, `secret`), and validate `TWO_FACTOR_ENCRYPTION_KEY` in `ProductionSecurityPolicy::assertEnvironment()`.
- [x] Flip `APP_DEBUG` default to `false` in `config/app.php`, unify early vs runtime error-handler debug resolution in `ApplicationFactory`, and assert `APP_DEBUG` in `ProductionSecurityPolicy`.
- [x] Unify rate limiting across API login, HTML admin login, and 2FA challenge into a single canonical enforcement architecture; reconcile contradictory docblock in `RateLimitReportOnlyAdminMiddleware`.
- [x] Automated secret generation and validation command (`bin/zoosper security:generate-secrets`, alias `secrets:generate`) that generates or audits cryptographically strong keys with `--write`, `--check`, and `--force` options.
- [x] Enforce Content-Security-Policy (`report_only: false`) after staging verification with Editor.js and admin assets.

## 6. Media

- [x] Media library + admin upload; Editor.js image integration
- [x] Media standalone package split — confirmed complete
- [x] Fixed: `MediaAdminController::upload()` silently swallowed all
  upload failures
- [x] **[FIXED] Media derivative processing (resize/transform) wired** —
  `services.php` configures `MediaUploadDerivativeDispatcher` and injects it into `MediaUploadService`. Duplicate MediaUploadService construction is resolved. Derivative database persistence remains a separate follow-up.
- [x] **[FIXED] Both media upload controllers receive container-configured `MediaUploadService`**
  with derivative dispatcher, validator, storage, and cleanup wired in. Duplicate MediaUploadService construction is resolved.
- [x] Apply strict image dimension (8192x8192) and file size pre-checks before decode in `MediaUploadValidator` and `GdMediaProcessor` to prevent upload DoS. Moving GD derivative processing to an asynchronous queue/worker remains a separate operational follow-up.

## 7. Mail

- [x] SMTP mailer + logged mailer + email log repository/admin viewer
- [x] Mail diagnostics + Mailpit local testing

## 8. API

- [x] API module (Auth, ContentPage, Health, Me)
- [x] Headless API parity (roles, themes, url-rewrites CRUD)
- [x] ContentPage API exposes structured Editor.js JSON (not serialized HTML)

## 9. Modular Asset Pipeline
- [~] **Module-owned admin grid assets are live through `/asset`.** Remaining:
  eliminate any runtime dependency on vendor edits, retain the package as the canonical source, retire the compatibility copy
  once package asset routing is live, and extend rendered-URL integration coverage.

- [x] Asset registry / resolver / controller (path-safe, MIME allowlist, ETag)
- [x] Wire `/asset/{module}/{path}` route + `asset()` helper live
- [x] Cache asset-registry scans per request
- [x] **Asset resolver adversarial coverage.** Core's `AssetResolver` already has traversal rejection and realpath containment, with direct and URL-encoded traversal tests. Extend coverage for null bytes and symlink edge cases; the containment layer itself is not missing.

## 10. Caching & Performance

- [x] **[FIXED] Real, configurable (file/Redis) cache foundation wired** —
  see §14/§15. `CacheDriverFactory` + `Zoosper\Core\Routing\
  CachingFallbackHandler` (opt-in frontend page cache, disabled by
  default).
- [x] **[FIXED] Unbounded `?page=` in `Pager::fromQuery()`** —
  `page_size` was clamped but `page` was not, allowing an arbitrarily huge
  `OFFSET`. Fixed with a fixed, generous safety ceiling (default 100,000),
  mirroring the existing `page_size` cap.
- [x] **[FIXED] No `COLLATE` pinned in generated `CREATE TABLE`** —
  `SchemaSqlBuilder` now explicitly pins `utf8mb4_unicode_ci` on the MySQL
  branch, preventing silently different collation behavior across
  environments running different MariaDB point releases. Only affects
  newly-created tables going forward, not existing live data.
- [x] Cache merged translation catalogue per locale
- [x] Rate-limit report sink rotation/retention (or DB store)
- [x] Vary page cache key by query string — wired via `Request::queryString()` accessor and `CacheContext` dimension partitioning.

## 11. Quality, Tooling & Repo Hygiene
- [x] Add JavaScript syntax validation for every shipped admin asset.
- [ ] Add DOM behavioural coverage for column drag, live reflection, locked
  anchors, dirty state and bookmark reload.
- [ ] Replace duplicate source-string tests with one behavioural contract.
- [x] Keep one canonical admin-grid column customisation guide rather than
  phase/hotfix documentation fragments (see `docs/admin.md`).
- [x] Add MySQL/MariaDB service container to `.github/workflows/quality-gate.yml` and run test suite against MySQL in CI.
- [x] Expand `psalm.xml` scan scope to include all first-party modules and packages (`zoosper-session`, `zoosper-global-announcements`, `zoosper-cache`, `zoosper-config`, etc.) and make Psalm a blocking CI gate.
- [x] Update crypto/2FA regression tests (`SecretProtectorKeyEnforcementTest`) to assert runtime behavioral enforcement rather than source-string matching.
- [x] Clean up stale doc comments in `bootstrap/autoload.php` and reconcile contradictory Supported Versions tables in `SECURITY.md`.
- [x] Evaluate PHP 8.5+ language floor vs 8.3/8.4 and document explicit technical requirements in README.

- [x] Pest + PHPUnit harness; quality gate runner
- [x] **`.gitattributes` (`export-ignore` for `tests/`/dev tooling) added
  to both extracted packages** (`zoosper-errors`, `zoosper-media`),
  matching Marko's own real package convention (confirmed by reading
  Marko's actual GitHub repos). Deliberately NOT added to `app/zoosper-*`
  modules yet — they are path-repository entries, not separately-exported
  packages, so the mechanism would be inert there today; add at the
  moment each module is actually extracted into `packages/`.
- [ ] **[R] Durable-tool manifest exists purely to stop cleanup automation
  from deleting scripts a Pest test asserts exist** — "inverted," per
  reviewer framing. Worth sitting with, not a quick fix.
- [x] Boot-and-serve feature test
- [ ] **[R2] ~150+ single-purpose tooling scripts still in `tools/`**,
  several existing solely to plan/audit deletion of other scripts in the
  same directory. Not yet pruned further.
- [x] **[R2] AI-session and completed one-shot cleanup scripts removed**
  in Phase 2C.
- [x] CI workflow (validate, Psalm, Pest+coverage, gate on every PR) — `.github/workflows/quality-gate.yml` runs full validation, JavaScript syntax checks, strict quality gate, Psalm baseline, Pest suite, and fresh-install smoke tests.
- [x] Fix composer `gate` script to `@php` (not hardcoded `php8.5`)
- [x] **[R2] Redundant `bin/pest.sh` removed; `composer test` remains the
  canonical test entry point.**
- [ ] **[R] Test-suite signal-to-noise ratio** — a `LegacyVerify*Test`
  family and a 15+ file Page Momentum test cluster are largely
  file-content-assertion "tests," not behavioral ones. Real, good tests do
  exist alongside them — this is about ratio, not total absence.
- [x] **[R] No public/internal API boundary between feature modules** —
  `CoreDecouplingArchitectureTest` and `FeatureModuleBoundaryArchitectureTest`
  enforce Core-to-feature and cross-feature boundary isolation.
- [x] **Comment-verbosity convention decided**: shift from detailed
  narrative "FIX (confirmed date, reviewer pass)" essays in source
  comments toward shorter, timeless doc-comments — full "why/when/who
  found it" story lives in commit messages and this roadmap's daily log
  instead. Documented as an explicit rule in `AGENTS.md`.
- [ ] **Production deployment process does not exist yet.** Confirmed:
  the project is still purely in local/dev-box development, with no build,
  package, or deploy step defined. This is the right time to design test-
  file exclusion from any deployable artifact (e.g. an `rsync --exclude`
  step, or `git archive` on a tagged release once modules are real,
  separate repos) directly into that process from day one — flagged as a
  real, open item to design once deployment planning begins, not solved
  yet.

## 12. Page Momentum (visible admin dashboard)

- [x] Routed `/admin/page-momentum` with real read-only facts
- [ ] **[R] Reviewer recommends deleting or radically shrinking this** —
  15+ test files/dozen classes for a static readiness page, vs. missing
  features like page delete. Explicit judgment call, not yet decided.

## 13. Consolidated "true-modular" roadmap (from reviewer passes)

1. Pick one canonical module-home convention (§1)
2. Extend the compile step beyond just the module list (§1, §15)
3. Real ALTER/removal support + FK declarations in the schema engine (§1, §2)
4. **[Substantially advanced]** Real security hardening:
   `EMULATE_PREPARES` ✅, pinned collation ✅, rate-limit salt ✅, sanitizer
   driver guard ✅, privilege-escalation fix ✅, race-condition fix ✅, 2FA
   key rotation ✅, Admin-login rate-limit enforcement ✅. Still open:
   account lockout and password reset.
5. **[Done]** Fixed the `role.manage`/`user.manage` privilege boundary
6. **[Done]** Verified the obsolete parallel 2FA family is already retired
7. **[Done]** Shared presentation contracts moved to Core; Page and Settings dropped `zoosper/admin`
8. **[Done]** Consolidate the two Grid systems and two AdminForm systems into one
9. Standardize module naming; real semver constraints instead of `*@dev`
10. **[Done]** Add delete/archive to every admin CRUD screen (Users, Roles, Sites, Pages all verified)
11. Enforce a public/internal API boundary between every pair of feature modules
12. CI pipeline gated on Pest, static analysis, architecture-boundary tests
13. Purge `tools/` to operational scripts only; resolve Page Momentum;
    remove AI-session tooling scripts from the repo (§0, §11)
14. **[Done]** Reconciled the `marko/framework` roadmap claim — root
    `composer.json` cleaned of every unused Marko package; real, verified
    adoption continues per-module (`zoosper/errors`, `zoosper/core`) — see §14
15. **[Done]** Extract logger (`marko/log`/`marko/log-file`) as the next
    `zoosper-core` → standalone-package candidate — see §14

---

## Open questions for the next planning session

1. **Production deployment process design** — genuinely still to be
   designed (see §11's new item). Key open question once ready: how will
   code actually reach a production server (git-based deploy, build
   artifact/zip, something else) — the right test-exclusion mechanism
   depends entirely on the answer.
2. **`marko/database-readwrite`'s real dependency on `marko/database`'s
   connection interface** — needs the actual `ConnectionInterface`/
   `TransactionInterface` source read before any further read-replica
   design, now that the "fully decoupled from `marko/database`" assumption
   has been corrected (see §14).
3. Rate-limit enforcement timeline, given the high-traffic assumption
   sharpening urgency vs. the ADR's "collect real report-only data first"
   precondition.

---

## External API-backed grids — start after current Grid closure

> **Sequence gate:** Do not begin this programme until the current admin Grid is
> completely closed. Grid closure means explicit keyed headers are shipped,
> package/application runtime compatibility work is resolved, the full test
> suite is green, browser drag and live reflection are verified, and the Grid
> closure commit is deployed.

- [x] **Phase 4ZJ — Data-source boundary.** Establish shared
  `GridDataSourceInterface`, `GridQuery`, `GridResult` and
  `GridDataSourceCapabilities` contracts for database-backed and external
  collections. Support numbered and cursor pagination without coupling the
  generic Grid package to HTTP or a particular API response shape. Preserve
  existing Pages Grid behaviour while proving the renderer is data-source
  agnostic.

- [x] **Phase 4ZK — `zoosper-api-grid` transport and mapping kernel.** Add an
  installable adapter package containing request, response, transport,
  authentication, row-mapping, reliability and descriptive exception
  contracts. Test with fake transports first; do not embed Orders-specific
  logic in the master package.

- [x] **Phase 4ZL — Declarative registration and reusable page composition.**
  Add API Grid definitions, registry, data-source adapter and admin page builder
  so a feature module can register an API-backed Grid without writing a custom
  Grid controller. Render filters, sorting, search and export only when the
  remote source declares those capabilities.

- [x] **Phase 4ZM — Store Orders pilot.** Build `/admin/store-orders` as the
  first real consumer using remote page and page-size parameters plus trusted
  store and website scope. Reuse saved views, configurable columns, locked
  anchors, live ordering and controlled error presentation. Do not implement
  misleading current-page-only search, filtering or sorting.

- [ ] **Phase 4ZN — Hardening and second pilot.** Prove the abstraction with a
  materially different API envelope or pagination model. Cover invalid JSON,
  timeouts, non-success responses, schema drift, response-size limits, secret
  and personal-data redaction, bounded exports, diagnostics and cursor
  pagination where available.

- [ ] **Phase 4ZO — Developer experience.** After two integrations validate the
  contracts, add `bin/zoosper make:api-grid`, readable scaffolding, reusable
  fixtures, an example module, integration documentation and an upgrade policy.

### API Grid architectural boundaries

- `zoosper-grid` owns generic definitions, criteria, pagination and rendering.
- `zoosper-admin-grid` owns the admin workspace, bookmarks, preferences,
  filters, export policy and browser runtime.
- `zoosper-api-grid` adapts external collection APIs into existing Grid data
  source contracts; it does not introduce another Grid framework.
- Feature modules own endpoint-specific request, response and row mapping,
  permissions and trusted context such as store or website scope.
- API base URLs and credentials never come from browser query parameters,
  templates, bookmarks or Grid definitions.
- Remote failures must remain distinguishable from valid empty results, and
  external payloads containing personal or transactional data must not be
  dumped into logs, exceptions, list HTML or default exports.

### API Grid definition of success

A module developer supplies an endpoint definition, authentication strategy,
request mapper, response mapper, row mapper, Grid columns and permissions.
Zoosper supplies route integration, admin layout, pagination, capability-aware
controls, saved views, column visibility and ordering, live reflection,
controlled failures, export policy, tests and diagnostics.

---

## External review response and public-launch priorities (2026-08-11)

The external senior-engineer review of commit `f4e93935fb17bf86c3126c44315453cfe1bf722a` was accepted as a launch-readiness input. Verified closures remain closed; roadmap summary lines must agree with detailed phase notes and current source.

### P0 before public announcement

- [x] Define and ship consistent archive/delete lifecycle behaviour for Pages, Admin Users, Roles, Sites, Site Domains, and Media, preserving audit history while gating irreversible deletions.
- [x] Add declarative-schema foreign-key support with typed MySQL/SQLite constraint generation and mergeable module schema manifests.
- [x] Update `SECURITY.md` to identify `v0.1.0-alpha.1` as the latest tagged pre-release while stating that no stable release has shipped.
- [x] Rewrite the root README current-state and included-capabilities sections for the alpha release, CI, Menu, Page revisions, docs site, Marko adoption, and explicit launch blockers.

### P1 before stable

- [x] Emit an explicit diagnostic for every cross-layer module identity override before further package extraction — completed in Phase 9GC with descriptive fail-closed coverage for app/modules/vendor layer pairings.
- [x] Either wire a production Media derivative processor and enablement policy or remove/de-scope the inactive processing surface.
- [x] Consolidate the two Grid systems and adopt the extensible Grid model across remaining eligible Admin screens.
- [x] Consolidate the two Admin Form systems and adopt the section/processor model across remaining forms.
- [x] Graduate Admin login rate limiting from report-only observation to an enforced, tested policy — `RATE_LIMIT_MODE=enforce` supported with generic 429 and `Retry-After` header.
- [x] Add password policy and `password_needs_rehash()` upgrade support.
- [x] Decide and document the CSRF model for stateful session-based `/api/*` routes.
- [x] Fail closed in production when secure-session configuration is absent or unsafe.
- [x] Investigate and consolidate environment loading across `Core\Bootstrap\EnvLoader`, `Core\Env`, and the global `env()` helper — consolidated in `bootstrap/autoload.php` and verified by architecture tests.
- [x] Register the project error handler before module discovery and module configuration execution — `ApplicationFactory::create()` registers `ErrorHandler` before module discovery, config loading, and database connection.

### P2 process and hygiene

- [x] Add a mechanical roadmap-summary drift check based on structured status identifiers, not fuzzy prose matching — validated in `RoadmapStructureIntegrityTest`.
- [x] Commit a Psalm baseline and prevent new advisory errors while reducing the existing baseline.
- [x] Make `composer.json` and `composer.lock` the source of truth for the dependency scope stated in `SECURITY.md`.
- [ ] Record behavioural assertion/test coverage evidence for the historical test-file reduction.
- [ ] Define semver constraints for extracted first-party packages instead of publishing `dev-dev` as the only compatibility signal.

### Deferred while launch blockers are active

- [ ] Resume Phase 9HF Marko dashboard widget adoption after the P0 lifecycle and integrity foundation is underway.

---

## Pre-launch technical code review and security audit response (2026-08-31)

An exhaustive independent technical review and static security teardown (`var/log/Claude-review.md` and `var/log/grok-review.md`) was conducted across the `dev` branch (~50,400 LOC first-party PHP across 16 `app/` modules and 12 `packages/` packages). The findings establish that while the modular architecture and automated testing foundations are mature (~1,627 tests / 11,500+ assertions), multiple critical launch blockers and structural reliability gaps must be remediated prior to any public production release.

### Critical Launch Blockers (P0 — Immediate Remediation Required)

- [x] **CRIT-01: HTML Sanitizer fail-open vulnerability in Page save pipeline.**
  - *Location:* `app/zoosper-page/src/Application/Save/PageSaveInput.php:41-42` and `app/zoosper-page/config/services.php:40-43`.
  - *Problem:* `PageSaveCoordinator` treats `HtmlSanitizerInterface` as an optional dependency; if unresolvable, the nullsafe chain falls back to raw form input (`$sanitizer?->sanitise(...)->toString() ?? $form['content']`). That unescaped content is subsequently rendered via Latte's `|noescape` in frontend templates (`page.latte` and `view.latte`), allowing stored XSS on any container misconfiguration.
  - *Remediation:* Make `HtmlSanitizerInterface` a mandatory, non-nullable constructor dependency in `PageSaveCoordinator` and `PageSaveInput` and throw `ZoosperException` (fail closed) if unresolvable. Never fallback to raw user input.
- [x] **CRIT-02: 2FA encryption key insecure fallback and placeholder exposure.**
  - *Location:* `config/two_factor.php:62`, `app/zoosper-two-factor/config/services.php:65-83`, `.env.example`.
  - *Problem:* `config/two_factor.php` silently falls back to `APP_KEY` (documented as `change-me-before-production`) when `TWO_FACTOR_ENCRYPTION_KEY` is unset. The service factory only guards against empty strings and does not reject known placeholder values. `ProductionSecurityPolicy::assertEnvironment()` never validates `TWO_FACTOR_ENCRYPTION_KEY` on boot.
  - *Remediation:* Remove `APP_KEY` fallback from `config/two_factor.php`; enforce placeholder blocklist (`change-me`, `change-me-before-production`, `secret`, `changeme`); add `TWO_FACTOR_ENCRYPTION_KEY` validation to `ProductionSecurityPolicy::assertEnvironment()`; update test assertions to verify runtime failure rather than source-string matching.
- [x] **CRIT-03: `APP_DEBUG` insecure default, dual boot-time computation, and missing policy assertion.**
  - *Location:* `config/app.php:19`, `app/zoosper-core/src/Bootstrap/ApplicationFactory.php:61-64` vs `:72-75`, `app/zoosper-core/src/Http/ProductionSecurityPolicy.php`.
  - *Problem:* `config/app.php` defaults `debug` to `true` when `APP_DEBUG` is unset. `ApplicationFactory::create()` calculates debug mode twice with conflicting logic (early handler defaults `false`, runtime handler defaults `true`). `ProductionSecurityPolicy::assertEnvironment()` does not validate `APP_DEBUG` for production environments.
  - *Remediation:* Change `config/app.php` default to `false`; unify error-handler debug resolution in `ApplicationFactory`; assert `APP_DEBUG=false` in `ProductionSecurityPolicy::assertEnvironment()` for `staging` and `production`.
- [x] **Referential integrity & database cascade reconciliation.**
  - *Remediation:* Declarative schema foreign keys (`SchemaForeignKey`, `SchemaSqlBuilder`, `SchemaValidator`) with typed constraints, restrict/cascade semantics, and application lifecycle checks are implemented across entities.
- [x] **CSP enforcement graduation.**
  - *Remediation:* CSP graduated from `report_only` to full enforcement; default configured to `false` in `config/security.php` and `.env.example`.

### High-Priority Reliability & Architecture Remediation (P1)

- [x] **HIGH-01: Database production-driver policy enforcement.**
  - *Location:* `config/database_policy.php`.
  - *Problem:* `DATABASE_ENFORCE_MYSQL_PRODUCTION`, `DATABASE_PRODUCTION_DRIVER`, and `DATABASE_ALLOW_SQLITE_LOCAL` are defined but never read by any code.
  - *Remediation:* Enforce driver checks in `ConnectionFactory` and `ProductionSecurityPolicy::assertEnvironment()` to prevent SQLite from running in production.
- [x] **HIGH-02: CI database engine parity with production.**
  - *Location:* `.github/workflows/quality-gate.yml`.
  - *Problem:* CI workflows execute all tests exclusively against SQLite (`DB_DRIVER: sqlite`), never verifying MySQL collation, JSON handling, or locking.
  - *Remediation:* Add a MySQL service container to the GitHub Actions workflow and run feature test suites against MySQL.
- [x] **HIGH-03: Unified admin authentication rate limiting.**
  - *Location:* `AuthController.php`, `RateLimitReportOnlyAdminMiddleware.php`, `AdminTwoFactorChallengeController.php`.
  - *Problem:* API login, HTML admin login, and 2FA challenge use disparate rate-limiting invocation patterns; middleware docblock contradicts its actual blocking behavior.
  - *Remediation:* Unify rate limiting into a single canonical middleware/service enforcement point across all three surfaces and update documentation.
- [x] **HIGH-04: Admin user locale persistence regression test net.**
  - *Problem:* Historical git log shows 11+ iterative hotfixes for locale persistence due to lack of behavioral regression coverage.
  - *Remediation:* Build a dedicated behavioral regression test suite testing locale parsing, hydration, pipeline update, and session propagation.
- [x] **HIGH-05: Consolidation of 14 duplicate environment reader closures.**
  - *Location:* 14 files in `config/*.php`.
  - *Problem:* Local `$env` closures in config files treat empty strings (`$_ENV[$key] !== ''`) as unset, conflicting with canonical `env()` in `bootstrap/autoload.php`.
  - *Remediation:* Remove local `$env` closures and have all config files call the global canonical `env()` helper.
- [x] **Automated secrets generation and validation command.**
  - *Remediation:* Implemented `bin/zoosper security:generate-secrets` (alias `secrets:generate`) to generate and audit cryptographically strong keys (`APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY`, `RATE_LIMIT_IDENTITY_SALT`, `CACHE_ENCRYPTION_KEY`) with `--write`, `--check`, and `--force` options.
- [x] **Offload synchronous GD derivative generation from upload request path.**
  - *Location:* `packages/zoosper-media/src/Processor/GdMediaProcessor.php`, `MediaUploadService.php`.
  - *Problem:* Synchronous image decoding and derivative generation during multipart uploads creates CPU/memory bottlenecks and DoS vectors.
  - *Remediation:* Enforce pre-decode dimension and byte bounds, and introduce asynchronous queued derivative processing.

### Medium-Priority Maintainability & Performance Gaps (P2)

- [x] **MED-01: Psalm static analysis scope expansion & blocking CI gate.**
  - *Location:* `psalm.xml`, `.github/workflows/quality-gate.yml`.
  - *Remediation:* Added all 16 first-party `app/` modules and 12 `packages/` packages to `psalm.xml` covering the entire codebase.
- [x] **MED-02: Templating engine discipline and variable extraction.**
  - *Location:* `PhpTemplateEngine.php`, `RoleAdminController.php`.
  - *Remediation:* Standardized view rendering on Latte across all Auth views including `RoleAdminController` (`form.latte`, `index.latte`, `permission-tree.latte`, `user-assignment.latte`), enforcing Latte's auto-escaping discipline.
- [x] **MED-03: Behavioral runtime assertions for crypto & security tests.**
  - *Location:* `SecretProtectorKeyEnforcementTest.php`.
  - *Problem:* Tests assert source code strings rather than runtime behavior under insecure environment configurations.
  - *Remediation:* Refactored security tests to assert actual container and service runtime rejection across empty keys and known placeholders (`change-me`, `secret`, `changeme`, `placeholder`).
- [x] **Admin Grid & Form kernel consolidation.**
  - *Remediation:* Abstracted into a single generic, typed Grid kernel and Form section/processor across all admin screens. Introduced `AdminFormRegistry`, `AdminFormDefinition`, and `AdminFormRenderer`. Aligned `zoosper-auth` and `zoosper-page` with modern `GridCompactWorkspaceRenderer`.
- [x] **Production manifest & route compilation.**
  - *Remediation:* Aggregated services and routes are compiled into `var/cache/` to eliminate per-request module iteration and filesystem overhead.
- [x] **Session lifecycle controls.**
  - *Problem:* PHP session defaults lack explicit absolute session timeouts and concurrent session bounds.
  - *Remediation:* Added configurable `ADMIN_SESSION_ABSOLUTE_LIFETIME` (`session_absolute_lifetime`), idle timeout resetting on active navigation, and password-update session invalidation in `SessionGuard`.
- [x] **PHP 8.5+ runtime requirement evaluation.**
  - *Problem:* Hard requirement on PHP 8.5 narrows hosting compatibility without demonstrated necessity.
  - *Remediation:* Evaluated runtime requirements and explicitly documented PHP 8.5+ language floor, required extensions, and database engine baseline in README.

### Low / Process & Documentation Hygiene (P3)

- [x] **LOW-01: Commit labeling hygiene and review attribution.**
  - *Remediation:* Ensure distinct, descriptive commit messages and co-author attribution across all branch merges.
- [x] **LOW-02: Stale comments & documentation reconciliation.**
  - *Remediation:* Remove obsolete comments in `bootstrap/autoload.php` referencing non-existent EnvLoader classes; reconcile contradictory Supported Versions tables in `SECURITY.md`.
- [x] **LOW-03: Clarify pluggable module architecture & `modules/` placeholder.**
  - *Remediation:* Documented the distinct roles of `app/` (internal modules), `packages/` (standalone Composer packages), and `modules/` (pluggable drop-in extensions) in `docs/modules.md`.
