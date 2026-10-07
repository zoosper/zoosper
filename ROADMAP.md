# Zoosper CMS closure roadmap

**Last updated:** 2026-10-07 (Sydney)

This file tracks only work required to close the current Zoosper CMS programme without adding new product features. Shipped history belongs in [CHANGELOG.md](CHANGELOG.md), current product and operating guidance belongs in [docs/](docs/README.md), and immutable release state belongs in Git tags.

## Current state

- Latest immutable pre-release: `v0.3.2-alpha.3` at `4676b012bc579d6f66f20aa317c4da862d5f045a`.
- Supported development branch: `dev`, preparing the public-alpha `v0.3.2-alpha.4` release candidate.
- Release-preparation parent commit: `ab49d52f5c60e759a00af982b879d68c82a5ee44`.
- No stable release has shipped.
- PHP 8.5, blocking full-scope Psalm, complete Pest, JavaScript behaviour/syntax gates, dual SQLite/MySQL release evidence, deterministic production artifacts, upgrade proofs, security hardening, canonical documentation, and production operations guidance are already established.
- Current Psalm baseline: `1,039` entries. It is stale-entry rejecting and cannot grow unnoticed.

## Closure definition

Zoosper CMS is considered wrapped without new features when every required item below is complete, the final development line passes all release gates, canonical documentation states the supported contract accurately, and the resulting release is merged and tagged without changing immutable historical releases.

Closure does not mean that every possible enhancement has been built. It means the current feature set is secure, supportable, distributable, documented, upgradeable, operationally verifiable, and released with no known required repository work left open.

## Required remaining work

### C1. Repository and legal ownership

- [ ] **C1.1 Commit provenance and employer-IP disposition.** Obtain human legal or contractual sign-off. Record only the outcome and any required repository action. Do not rewrite published history merely to hide existing metadata.
- [ ] **C1.2 Repository-owner security controls.** Confirm the intended dependency, code-scanning, secret-scanning, branch-protection, and release-permission settings in the repository host. These are owner-admin controls rather than source-code features.

### C2. Static-analysis debt closure

- [~] **C2.1 Psalm zero-baseline programme.** Reduce the current `1,039`-entry baseline issue-family by issue-family. Each removal requires source-boundary review, focused behavioural or architecture evidence, blocking full-scope Psalm, stale-entry rejection, the complete test suite, and strict quality.
- [~] **C2.2 Behaviour-sensitive cast cohort.** PDO driver-name handling is complete. Continue with bounded request/form/configuration/URL scalar-normalisation cohorts before unrelated Media, persistence-ID, CSRF, and error-payload findings.
- [ ] **C2.3 Zero-baseline acceptance.** Remove `psalm-baseline.xml` only when a clean blocking analysis passes without suppressing real defects or weakening runtime validation.

### C3. Stable distribution and compatibility contract

- [ ] **C3.1 Distribution model.** Choose and document either published first-party Composer packages or a supported monorepo/path-package consumer model. Include the supported third-party module installation and update workflow.
- [ ] **C3.2 Compatibility policy.** Define the stable public API, extension, configuration, database schema, deprecation, and semantic-versioning guarantees.
- [ ] **C3.3 Upgrade and support policy.** Define supported release lines, security-fix policy, upgrade paths, rollback expectations, and minimum runtime/database requirements for the first non-alpha release.

### C4. Operational closure

- [ ] **C4.1 Media queue observability.** Provide operationally usable depth, failure, retry, and processing-latency visibility for the existing Media queue before claiming high-volume production readiness.
- [ ] **C4.2 Production acceptance record.** Execute and record the operator-runbook checks in the target environment, including enforcing CSP browser workflows, trusted proxies, selected cache backend, SMTP and `APP_URL`, secret policy, public-webroot isolation, MySQL migration and foreign-key integrity, queue workers, scheduled maintenance, and post-deploy health.
- [ ] **C4.3 External dependency and disclosure review.** Confirm locked dependency audit status and that the private security-reporting path remains operational for the release.

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
