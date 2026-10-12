# Zoosper CMS closure roadmap

**Last updated:** 2026-10-12 (Sydney)

This file tracks only work required to close the current Zoosper CMS programme without adding new product features. Shipped history belongs in [CHANGELOG.md](CHANGELOG.md), current product and operating guidance belongs in [docs/](docs/README.md), and immutable release state belongs in Git tags.

## Current state

- Latest immutable pre-release: `v0.3.2-alpha.4` at `74db4e87c43999da84e59329dab6ff83d399dbb6`.
- Previous immutable pre-release: `v0.3.2-alpha.3` at `4676b012bc579d6f66f20aa317c4da862d5f045a`.
- Supported development branch: `dev`, continuing required closure work after alpha.4 publication; no next release version has been selected.
- Alpha.4 publication and public artifact checksum verification are complete. Production deployment acceptance and C1-C5 programme closure remain open.
- No stable release has shipped.
- PHP 8.5, blocking full-scope Psalm, complete Pest, JavaScript behaviour/syntax gates, dual SQLite/MySQL release evidence, deterministic production artifacts, upgrade proofs, security hardening, canonical documentation, and production operations guidance are already established.
- Current Psalm baseline: `917` code entries at development commit `9f3d4dd857f57357795bd3bd3fec582f9114c55a`. It is stale-entry rejecting and cannot grow unnoticed; refresh this measured value whenever a verified cohort changes `psalm-baseline.xml`.

## Closure definition

Zoosper CMS is considered wrapped without new features when every required item below is complete, the final development line passes all release gates, canonical documentation states the supported contract accurately, and the resulting release is merged and tagged without changing immutable historical releases.

Closure does not mean that every possible enhancement has been built. It means the current feature set is secure, supportable, distributable, documented, upgradeable, operationally verifiable, and released with no known required repository work left open.

## Required remaining work

### C1. Repository and legal ownership

- [ ] **C1.1 Commit provenance and employer-IP disposition.** Obtain human legal or contractual sign-off. Record only the outcome and any required repository action. Do not rewrite published history merely to hide existing metadata.
- [ ] **C1.2 Repository-owner security controls and independent review.** Confirm dependency, code-scanning, secret-scanning, branch-protection, required-review, required-check, force-push, and release-permission settings in the repository host. Add reviewed ownership for security-sensitive paths and obtain visible independent review of Auth, two-factor, upload, sanitisation, and release-sensitive changes before a stable claim. These are owner-admin and assurance controls rather than claims that source alone can prove.

### C2. Static-analysis debt closure

- [~] **C2.1 Psalm zero-baseline programme.** Reduce the current `917`-code-entry baseline issue-family by issue-family. Each removal requires source-boundary review, focused behavioural or architecture evidence, blocking full-scope Psalm, stale-entry rejection, the complete test suite, and strict quality. Refresh the measured count after each committed reduction instead of preserving a historical number.
- [~] **C2.2 Behaviour-sensitive cast cohort.** PDO driver-name handling is complete. Continue with bounded request/form/configuration/URL scalar-normalisation cohorts before unrelated Media, persistence-ID, CSRF, and error-payload findings.
- [ ] **C2.3 Zero-baseline acceptance.** Remove `psalm-baseline.xml` only when a clean blocking analysis passes without suppressing real defects or weakening runtime validation.

### C3. Stable distribution and compatibility contract

- [ ] **C3.1 Distribution model.** Choose and document either published first-party Composer packages or a supported monorepo/path-package consumer model. Include the supported third-party module installation and update workflow.
- [ ] **C3.2 Compatibility policy.** Define the stable public API, extension, configuration, database schema, deprecation, and semantic-versioning guarantees.
- [ ] **C3.3 Upgrade and support policy.** Define supported release lines, security-fix policy, upgrade paths, rollback expectations, and minimum runtime/database requirements for the first non-alpha release.

### C4. Operational closure

- [ ] **C4.1 Media queue observability.** Provide operationally usable depth, failure, retry, and processing-latency visibility for the existing Media queue before claiming high-volume production readiness.
- [ ] **C4.2 Production acceptance record.** Execute and record the operator-runbook checks in the target environment, including enforcing CSP browser workflows, trusted proxies, selected cache backend, SMTP and `APP_URL`, secret policy, public-webroot isolation, MySQL migration and foreign-key integrity, queue workers, scheduled maintenance, and post-deploy health.
- [~] **C4.3 External dependency and disclosure review.** The current development lock resolves `source-map-js` to patched version `1.2.2` after reviewing GHSA-68fv-2mgg-jv7q. Reconfirm clean locked Composer and npm audits and exercise the private security-reporting path for the release.
- [ ] **C4.4 Verified security-assurance residuals.** Close or explicitly accept the source-confirmed review residuals before a stable claim: require the Authentication middleware in production Admin composition, make non-public Admin access classification explicit rather than permission-empty by default, align runtime and secret-audit strength rules, decide the disabled-by-default Store Orders distribution contract, require the production TOTP replay-claim dependency, and define safe handling for overlapping parameter-route shapes and decoded route values. Keep unverified or host-only review claims labelled as such until executable or owner evidence exists.

### C5. Final release closure

- [ ] **C5.1 Documentation truth pass.** Align README, SECURITY, current-development documentation, package READMEs, release checklist, operator runbook, and generated documentation with the final supported contract. Completed implementation history remains in the changelog and tags, not here.
- [ ] **C5.2 Final release rehearsal.** Pass Composer validation/audit, JavaScript gates, strict quality, blocking Psalm without a baseline, complete Pest, SQLite and target-MySQL upgrades, fresh install, compiled manifest, documentation build, deterministic artifact, and isolated locked-install checks.
- [ ] **C5.3 Release decision.** Decide whether the completed line is the final alpha/beta candidate or the first stable release based on C1-C5 evidence and the published compatibility policy.
- [ ] **C5.4 Release execution.** Create the release commit, merge to `master`, create and verify the immutable annotated tag, publish artifacts/documentation as applicable, then either close development or open only a deliberately approved maintenance line.

## Explicitly not required for closure

The following are new capabilities or later scale choices. They must not block wrapping the current CMS unless separately approved:

- Invisible CAPTCHA or another bot-protection provider.
- Read replicas, `marko/database` adoption, or long-lived-worker support.
- Additional API Grid integrations or new external-service pilots.
- Form Builder, broader API resources, new editor blocks, or other product features.
- New Admin themes, dashboards, announcement capabilities, or presentation redesigns.
- Additional package extraction that is not required by the chosen distribution contract.

## Established foundation

The current codebase already includes the CMS, Admin, Auth and 2FA, Sites, Pages, revisions, Menu, Media and derivatives, Themes, SEO, URL rewrites, Mail, Settings, scoped configuration, PAT/API capabilities, module lifecycle and extension boundaries, Admin Grid/Form kernels, API Grid foundations, caching, security controls, audit/history, deterministic artifacts, upgrade assurance, documentation publishing, and production operator guidance.

For authoritative detail, use:

- [Changelog](CHANGELOG.md) for shipped work and historical phases.
- [Documentation index](docs/README.md) for current product, architecture, developer, security, deployment, and operating contracts.
- [Current development line](docs/releases/current-development.md) for the active release identity.
- [Release checklist](docs/release-checklist.md) for executable release gates.
- [Production operator runbook](docs/operations/production-operator-runbook.md) for target-environment acceptance.
- [Security policy](SECURITY.md) for supported versions and private disclosure.

## Working rule

Do not add a new roadmap item unless it is required to close C1-C5 or the project owner explicitly approves a new feature. When an item is completed, record the implementation in the changelog or durable documentation and keep this roadmap focused on remaining closure work.
