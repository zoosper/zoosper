# Release checklist

- Complete the canonical [production operator runbook](operations/production-operator-runbook.md) and its [production acceptance record](operations/production-acceptance-record.md) for one exact target deployment; retain only secret-free evidence
- Clean worktree and reviewed changelog
- Locked Composer installation, validation and audit
- Deterministic production artifact build, prohibited-path audit, isolated extraction verification, release manifest and SHA-256 validation
- Full Pest suite (including MySQL and SQLite integration test legs)
- JavaScript syntax and strict repository gate
- Static analysis baseline status review (Psalm zero-baseline drive or reviewed residual)
- Disposable fresh-install smoke and containerized production boot verification
- Foreign key referential integrity status check (`schema:foreign-keys:status`)
- Secret presence, strength, and placeholder validation (`APP_KEY`, `TWO_FACTOR_ENCRYPTION_KEY`, `RATE_LIMIT_IDENTITY_SALT`, and Redis-only `CACHE_ENCRYPTION_KEY`)
- Redis production policy when selected: authenticated staging/production Redis, strong signed-value key, secret-safe diagnostics, and no Redis requirements for the file driver
- Content Security Policy (CSP) enforcement verification (`report_only => false`)
- Media derivative queue atomic single-winner claims, 15-minute stale-lease recovery, the three-attempt failure boundary, non-zero failure exit, repeatable observability migration, SQL-bounded latency aggregation, pre-decode limit checks, and `media:queue:status --format=json` evidence for depth, retries, failures, oldest-pending age, queue wait, and processing latency
- Module asset pipeline adversarial path-normalisation and extension-allow-list tests
- Module manifest compilation and freshness (`module:manifest:check`)
- Release checks under production-safe configuration (`release:check`)
- Live frontend, API health and Admin login checks
- Authenticated Admin smoke journey
- Database and Media rollback points
- Globally unique migration basenames and disposable release-upgrade rehearsal
- Annotated immutable release tag

## Release recommendation and approval protocol

Every verified development cohort ends with one explicit release judgement recorded in the handoff or verification evidence:

- **Continue on `dev`** when required closure work remains or the cohort is not a coherent release boundary.
- **Prepare a release candidate rehearsal** when the accumulated line is coherent and documentation truth, upgrade/fresh-install evidence, static analysis, tests, quality, package readiness, and operational checks are ready to be rerun against one exact commit.
- **Recommend an immutable tag** only after the exact candidate commit passes the complete release checklist, produces deterministic artifact and checksum evidence, has accurate release identity and documentation, and has no unresolved blocker for the proposed release channel.
- **Recommend promotion to `master`** only after the annotated tag, public release metadata, artifact, and checksum resolve to the same approved commit and the ancestry checks in the release-promotion runbook pass.
- **Hold release** whenever evidence is missing, conflicting, stale, or identifies a release blocker; record the blocker and the next bounded verification instead of guessing.

The recommendation is advisory. Creating or moving a tag, publishing a release or artifact, promoting `master`, and deploying remain explicit release-owner approvals. Never retag an immutable release, promote an untagged development tip, or merge post-release `dev` work into `master` as part of promotion.

### Evidence required for a tag recommendation

- Clean worktree with local `HEAD` and `origin/dev` aligned on the exact candidate commit.
- Release identity, changelog, roadmap, current-development page, Admin version, and API health aligned.
- Blocking Psalm with stale-baseline rejection, complete Pest, strict quality, JavaScript gates, Composer validation/audit, and package-readiness checks green.
- Fresh-install, latest-release upgrade, supported older-release upgrade, target-MySQL migration/foreign-key, documentation-build, and isolated locked-install evidence green where required by the release channel.
- Two deterministic production-artifact builds with matching SHA-256, verified manifest, prohibited-path audit, and isolated extraction acceptance.
- Security and operations review complete for the intended channel, including unresolved C1-C5 items explicitly classified as blocker, accepted prerelease limitation, or deferred non-release work.

### Promotion recommendation

A tag recommendation and a `master` promotion recommendation are separate decisions. Do not recommend promotion until the immutable tag and published release have been independently verified. Promote `master` only to the peeled release commit by the exact process in `docs/releases/release-promotion.md`; preserve later `dev` commits on `dev`.

## Starter experience
- Run `php8.5 bin/zoosper starter:install` twice and verify the second run retains the Site and Pages.
- Verify `/` and `/about` render through the default starter theme.
- Verify the application-owned session path and starter-theme files pass `release:check`.

## Reusable final tag gate

```bash
RELEASE_TAG=<candidate-tag>
NEXT_VERSION=<next-development-version>
```
- Confirm `php8.5 bin/zoosper version` reports the release version represented by `RELEASE_TAG`.
- Confirm Admin and API health expose the same authoritative version.
- Run the full test suite, quality gate, fresh-install smoke, compile, manifest check, release check, and documentation build.
- Record manual browser evidence for login/logout, application-owned sessions, Home/About rendering, Page revision restore, Menu rendering, and Media lifecycle.
- Commit and push the release identity, require a clean working tree, then create annotated tag defined by `RELEASE_TAG`.

### Foreign-key release gate

- Run `php8.5 bin/zoosper schema:foreign-keys:status --format=json` and require `add=0`, `mismatch=0`, and `sqlite_rebuild_required=0`.
- Confirm `php8.5 bin/zoosper release:check` reports a passing `foreign-keys` result with the same counts.
- Do not retry a failed MySQL apply blindly; inspect status because earlier DDL statements may already have succeeded.
- For SQLite, use explicit data-preserving rebuild migrations for existing-table foreign-key changes.
- Before release, compile the module manifest so the independent `module-manifest` readiness check is fresh.

## Production platform verification

- Run `composer check-platform-reqs --no-dev` and confirm the production PHP runtime provides cURL, GD, mbstring, PDO and PDO MySQL.

- [ ] Protected API negative paths preserve 401/403 semantics, authorisation-before-lookup, and single-attempt 2FA enrolment.
