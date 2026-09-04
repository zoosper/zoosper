# Current release source

## Version

`0.3.0-alpha.5`

## Release status

- The `v0.3.0-alpha.5` release source is prepared on `dev`.
- The release commit, immutable annotated tag, push, and `0.3.0-alpha.6-dev` opening remain separate Phase 12R operations.
- Zoosper remains public alpha software. No stable release has shipped, and no deployment or GitHub release publication is claimed by this source update.

## Delivered in alpha.5

- Closed the current first-party relationship inventory with 33 declarative foreign keys, MySQL reconciliation, fresh SQLite parity, and fail-closed release checks.
- Made Psalm a blocking full-scope CI gate and expanded dual-engine and architecture verification.
- Added automated cryptographically strong secret generation and mandatory production-safety validation.
- Formalised absolute Admin session lifetime controls and password-change invalidation.
- Expanded adversarial module-asset path and extension coverage.
- Completed the Fable-informed Admin collection/workspace rollout without weakening routes, ACL, CSRF, persistence, audit, or feature ownership.
- Replaced feature-side duplicate shell-title JavaScript and CSS workarounds with a backwards-compatible server-owned policy.
- Removed production inline Menu and Grid presentation and retired the unused phase-era `HomeController`; the active Page and Theme fallback chain remains authoritative.

## Verification evidence

- Full Pest suite: 1,683 passed, 2 skipped, 12,400 assertions.
- Strict quality gate: 0 errors and 0 warnings.
- Composer root manifest: valid.
- Composer dependency audit: no security vulnerability advisories.
- JavaScript syntax: 45 shipped assets validated during the broad gate.
- Module manifest: compiled, fresh, 39 modules.
- Foreign-key status: 33 present, 0 additions, 0 mismatches, 0 SQLite rebuild requirements.
- Alpha release check: passed.

## Next development line

After the immutable `v0.3.0-alpha.5` release commit and tag are pushed and verified, open `0.3.0-alpha.6-dev` in a separate development commit. Keep architecture documentation, package READMEs, upgrade notes, and operational evidence current in every phase.
