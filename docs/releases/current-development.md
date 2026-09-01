# Current release line

## Version

`0.3.0-alpha.4`

## Current source status

- `v0.3.0-alpha.4` is the current release identity, preserving the published API, security, and package-ownership baseline while adding module-discovery caching, CSP enforcement, unified AdminFormRenderer, asynchronous media derivative offloading, database-backed Module Lifecycle kernel, first-party package extractions, PHP 8.5 compatibility, and pre-launch security hardening.
- Media list, detail, derivative, canonical upload, archive, restore, and guarded permanent-delete APIs are complete in the release line.
- Module-discovery collision handling has been reconciled against current source and remains fail closed.
- The responsive Admin refinement is complete in source at `364414a4878cde36fd89de8583326e4d1ff1f625`: permission-aware Dashboard links, fluid light/dark presentation, package-owned responsive Grid workflows, a sidebar-owned collapse control, module-owned semantic destination identifiers, and text-only non-interactive navigation groups.
- Final accepted verification for that source was `1,550` tests with `11,157` assertions; the standard quality gate passed `3` checks with `0` errors and `0` warnings.
- The Admin refinement was browser-accepted, committed, and pushed. It was not deployed as part of that phase.

## Release closure and next engineering phase

Phase 10AR is complete at `fcbfa4e736a1c25e1f0e97760507fd42b8294c77`. Deployment-provided process/container values are authoritative over `.env`; staging and production retain fail-closed session and rate-limit policy across HTTP and console boot. Release verification passed `1,557` tests / `11,175` assertions and the strict `3`-check quality gate with `0` errors and `0` warnings. Browser acceptance and production-safe console boot passed. The release-identity commit and immutable annotated `v0.3.0-alpha.3` tag are complete at `72dd1da44ea491c478ee76ab85dbe9fc286ebf99`; no GitHub release publication or deployment has occurred.

Subsequent `0.3.0-alpha.4-dev` milestones delivered:
- **Phase 10AU:** Aggregated discovery map cached in `var/cache/modules.php` for production boot optimization.
- **Phase 10AV:** Content Security Policy (CSP) enforcement (`report_only => false`).
- **Phases 10AW / 10AX:** Unified `AdminFormRenderer` with Danger Zone deletion, sections, and Editor.js support across User, Role, Site, and Page controllers.
- **Phase 10AY:** Asynchronous media derivative offload with `media_processing_queue`, `QueuedMediaProcessor`, `media:process-queue` worker, and GD pre-decode resource limits.
- **Phase 10AZ:** Database-backed Module Lifecycle kernel with `module:install`, `module:uninstall`, `module:enable`, and `module:disable` commands.
- **First-party module extractions:** Dynamic Dashboard widgets (`zoosper/admin-dashboard`), Decoupled Content Editor (`zoosper/editor`), and Global Announcements (`zoosper/global-announcements`).
- **Pre-launch audit remediation pass (2026-09-01):** Fail-closed HTML sanitization (CRIT-01), 2FA encryption key placeholder rejection and rotation (CRIT-02), `APP_DEBUG=false` production enforcement (CRIT-03), database driver production policy (HIGH-01), and canonical `env()` helper consolidation (HIGH-05).

Current engineering priorities from the 2026-09-01 re-audit and technical code review:
- Complete referential integrity and foreign-key reconciliation across all cross-module relationships (`schema:foreign-keys:status` / `apply`).
- Active CI test suite execution against MySQL alongside SQLite.
- Static analysis (Psalm) baseline burn-down toward an enforced zero-baseline gate.
- Automated secret generation command and mandatory boot validation under staging and production environments.
- Absolute session lifetime (`ADMIN_SESSION_ABSOLUTE_LIFETIME`) and concurrent session management.
- Unauthenticated module asset pipeline adversarial security tests.

Keep documentation, package READMEs, upgrade notes, and architecture decisions current in every phase.
