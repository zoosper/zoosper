# Current development line

## Version

`0.3.0-alpha.5-dev`

## Current source status

- `0.3.0-alpha.5-dev` is the current development identity. The immutable latest tagged pre-release remains `v0.3.0-alpha.4`, preserving the published API, security, package-ownership, module-discovery caching, CSP enforcement, unified AdminFormRenderer, asynchronous media derivative offloading, database-backed Module Lifecycle kernel, first-party package extractions, PHP 8.5 compatibility, and pre-launch security hardening baseline.
- Media list, detail, derivative, canonical upload, archive, restore, and guarded permanent-delete APIs are complete in the release line.
- Module-discovery collision handling has been reconciled against current source and remains fail closed.
- The responsive Admin refinement is complete in source at `364414a4878cde36fd89de8583326e4d1ff1f625`: permission-aware Dashboard links, fluid light/dark presentation, package-owned responsive Grid workflows, a sidebar-owned collapse control, module-owned semantic destination identifiers, and text-only non-interactive navigation groups.
- Final accepted verification for `v0.3.0-alpha.4` was `1,619` tests with `11,635` assertions; the strict quality gate passed with `0` errors and `0` warnings.
- The `v0.3.0-alpha.4` release tag was committed, tagged, and pushed. It was not deployed as part of that phase.

## Release closure and next engineering phase

Phase 10AR and subsequent alpha milestones are complete. Deployment-provided process/container values are authoritative over `.env`; staging and production retain fail-closed session and rate-limit policy across HTTP and console boot. Release verification passed `1,619` tests / `11,635` assertions and the strict quality gate with `0` errors and `0` warnings. Browser acceptance and production-safe console boot passed. The release-identity commit and immutable annotated `v0.3.0-alpha.4` tag are complete at `c70f37dc`; no GitHub release publication or deployment has occurred.

Milestones delivered in `v0.3.0-alpha.4`:
- **Phase 10AU:** Aggregated discovery map cached in `var/cache/modules.php` for production boot optimization.
- **Phase 10AV:** Content Security Policy (CSP) enforcement (`report_only => false`).
- **Phases 10AW / 10AX:** Unified `AdminFormRenderer` with Danger Zone deletion, sections, and Editor.js support across User, Role, Site, and Page controllers.
- **Phase 10AY:** Asynchronous media derivative offload with `media_processing_queue`, `QueuedMediaProcessor`, `media:process-queue` worker, and GD pre-decode resource limits.
- **Phase 10AZ:** Database-backed Module Lifecycle kernel with `module:install`, `module:uninstall`, `module:enable`, and `module:disable` commands.
- **First-party module extractions:** Dynamic Dashboard widgets (`zoosper/admin-dashboard`), Decoupled Content Editor (`zoosper/editor`), and Global Announcements (`zoosper/global-announcements`).
- **Pre-launch audit remediation pass (2026-09-01):** Fail-closed HTML sanitization (CRIT-01), 2FA encryption key placeholder rejection and rotation (CRIT-02), `APP_DEBUG=false` production enforcement (CRIT-03), database driver production policy (HIGH-01), CI MySQL execution (HIGH-02), asset path traversal rejection, and canonical `env()` helper consolidation (HIGH-05/LOW-04).

Current engineering priorities from the 2026-09-01 re-audit and technical code review for `0.3.0-alpha.5-dev`:
- Continue the next integrity review as new modules and relationships are introduced; the current first-party inventory is closed with 33 declarative constraints, zero pending additions, zero mismatches, fresh SQLite parity, and release-check enforcement.
- Static analysis (Psalm) baseline burn-down toward an enforced zero-baseline gate.
- Automated secret generation command and mandatory boot validation under staging and production environments.
- Absolute session lifetime (`ADMIN_SESSION_ABSOLUTE_LIFETIME`) and concurrent session management.
- Unauthenticated module asset pipeline adversarial security tests.

Keep documentation, package READMEs, upgrade notes, and architecture decisions current in every phase.

## Phase 11A referential-integrity closure

Phase 11A is complete on the `0.3.0-alpha.5-dev` line:

- Existing migration-owned relationships were reconciled into module manifests without duplicate DDL.
- Ten audited missing relationships and two indexed creator relationships were applied to MySQL.
- The active database reports `present=33`, `add=0`, `mismatch=0`, and `sqlite_rebuild_required=0`.
- Fresh SQLite installation creates all 33 declarative foreign keys and passes `PRAGMA foreign_key_check` with no violations.
- Behavioural tests cover orphan rejection, `CASCADE`, `SET NULL`, and restrictive parent-key updates.
- `release:check` now fails closed when additions, mismatches, SQLite rebuild requirements, or inspection failures remain.
- The separate current release blocker is `module-manifest: status=missing`; compile the manifest before a release-readiness run.
