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
- Current Psalm baseline: `687` code entries measured by the Page dead-code C2 bulk cohort. It is stale-entry rejecting and cannot grow unnoticed; refresh this measured value whenever a verified cohort changes `psalm-baseline.xml`.

## Closure definition

Zoosper CMS is considered wrapped without new features when every required item below is complete, the final development line passes all release gates, canonical documentation states the supported contract accurately, and the resulting release is merged and tagged without changing immutable historical releases.

Closure does not mean that every possible enhancement has been built. It means the current feature set is secure, supportable, distributable, documented, upgradeable, operationally verifiable, and released with no known required repository work left open.

## Required remaining work

### C1. Repository and legal ownership

- [ ] **C1.1 Commit provenance and employer-IP disposition.** Obtain human legal or contractual sign-off. Record only the outcome and any required repository action. Do not rewrite published history merely to hide existing metadata.
- [ ] **C1.2 Repository-owner security controls and independent review.** Confirm dependency, code-scanning, secret-scanning, branch-protection, required-review, required-check, force-push, and release-permission settings in the repository host. Add reviewed ownership for security-sensitive paths and obtain visible independent review of Auth, two-factor, upload, sanitisation, and release-sensitive changes before a stable claim. These are owner-admin and assurance controls rather than claims that source alone can prove.

### C2. Static-analysis debt closure

- [~] **C2.1 Psalm zero-baseline programme.** Reduce the current `687`-code-entry baseline issue-family by issue-family. Each removal requires source-boundary review, focused behavioural or architecture evidence, blocking full-scope Psalm, stale-entry rejection, the complete test suite, and strict quality. Refresh the measured count after each committed reduction instead of preserving a historical number.
- [~] **C2.2 Behaviour-sensitive cast cohort.** PDO driver-name handling is complete. Continue with bounded request/form/configuration/URL scalar-normalisation cohorts before unrelated Media, persistence-ID, CSRF, and error-payload findings.
- [ ] **C2.3 Zero-baseline acceptance.** Remove `psalm-baseline.xml` only when a clean blocking analysis passes without suppressing real defects or weakening runtime validation.

### C3. Stable distribution and compatibility contract

- [x] **C3.1 Distribution model.** The root project is the supported synchronised monorepo/path-package distribution; tagged checkouts and deterministic production artifacts are the deployment units. The third-party and local-module installation/update workflow is canonical in `docs/compatibility-and-support.md`.
- [x] **C3.2 Compatibility policy.** Public API, extension, configuration, CLI, database, deprecation and semantic-versioning boundaries are defined in `docs/compatibility-and-support.md`, while preserving the current public-alpha status.
- [x] **C3.3 Upgrade and support policy.** Supported public-alpha lines, private security reporting, evidence-backed upgrade origins, rollback expectations, and minimum PHP/database requirements are defined in `docs/compatibility-and-support.md`. A stable support lifetime must still be selected before a stable tag.

### C4. Operational closure

- [x] **C4.1 Media queue observability.** `media:queue:status --format=json` provides secret-safe depth, retries, failures, oldest-pending age, queue wait and processing latency; the worker records start/completion timestamps and exits non-zero on batch failure. Deployment-owned alert thresholds remain part of C4.2 target acceptance.
- [ ] **C4.2 Production acceptance record.** Execute and record the operator-runbook checks in the target environment, including enforcing CSP browser workflows, trusted proxies, selected cache backend, SMTP and `APP_URL`, secret policy, public-webroot isolation, MySQL migration and foreign-key integrity, queue workers, scheduled maintenance, and post-deploy health.
- [~] **C4.3 External dependency and disclosure review.** The current development lock resolves `source-map-js` to patched version `1.2.2` after reviewing GHSA-68fv-2mgg-jv7q. CI blocks on locked Composer and development Node dependency audits. Repository policy defines private coordinated disclosure; release acceptance still requires an owner-verified exercise of `security@zoosper.com`.
- [x] **C4.4 Verified security-assurance residuals.** Source-confirmed residuals are closed with executable contracts: production Admin composition requires Authentication middleware; non-public Admin routes require explicit access classification; runtime and secret-audit strength rules share one policy; Store Orders is an optional integration included for compatibility testing but contributes no runtime surface unless a deployment explicitly opts in; every assembled TOTP challenge service requires atomic replay claiming; and parameter routes reject overlapping shapes plus unsafe decoded values while preserving explicit multi-segment captures. Host-only and independent-review claims remain governed by C1.2 and C4.2.

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
