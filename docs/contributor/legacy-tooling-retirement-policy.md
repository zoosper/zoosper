# Legacy Tooling Retirement Policy

**Effective:** Phase 1.22.

Zoosper is a lean, true modular CMS. Historical scaffolding is retired on a clear
policy so the foundation stays strong while coverage never drops.

## The four buckets

| Bucket | Matches (filename prefix / exact) | Rule |
|---|---|---|
| **DELETE_NOW** | `apply-*`, `add-*`, `export-phase-*`, `phase015-*`, `tests/run.php`, `*.bak` | Remove now. Do **not** create new ones - `apply-*`/`add-*` are the deprecated one-shot code-mod pattern. |
| **MIGRATE_TO_PEST** | `verify-*`, `run-verification-suite` | Retire each **only when** an equivalent Pest test exists and passes. Track with `bin/tools-inventory.php`. |
| **KEEP_OPS** | `diagnose-*`, `audit-*`, `repair-*`, `quarantine-*`, `clean-*`, `demo-*`, `inspect-*`, `send-*`, `reset-*`, `publish-*`, `migrate-*`, `assert-*`, `bootstrap*`, `wire-*`, `fix-*`, mailpit scripts | Keep. These are runtime operational/diagnostic tools, not tests. |
| **REVIEW** | anything unmatched | Classify manually - never leave silently mis-bucketed. |

## Suggested migration order (highest value first)

Retire the `verify-*` net in this order, writing a Pest test before deleting each:

1. Entity save pipeline (field definitions, extension persistence) - *partly done in 1.21*
2. Admin form section/processor registry extensibility
3. CSRF + ACL admin route gates
4. HTML sanitisation (write-time) - *done in 1.21*
5. Block JSON content model - *done in 1.21*
6. Schema engine / migrations
7. i18n / translation aggregation
8. Everything else, by usage frequency

## Carry-forward rule (from Phase 1.21)

> Every behavioural change ships with a Pest test. No new bespoke verifier scripts.

## Definition of done for tooling retirement

- [ ] All DELETE_NOW artifacts removed (via `bin/cleanup-legacy-tooling.sh --force`).
- [ ] `bin/tools-inventory.php` shows REVIEW = 0 (everything classified).
- [ ] Each retired `verify-*` has a passing Pest equivalent committed first.
- [ ] `MIGRATE_TO_PEST` count trends to 0 over subsequent phases.
- [ ] Operational tools optionally relocated under `tools/ops/` (future, optional).
