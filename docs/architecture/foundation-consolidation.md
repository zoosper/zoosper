# Foundation Consolidation (Phase 1.22)

## Rationale

Phase 1.21 delivered the Pest harness and first regression suite - the safety net
that makes cleanup safe. Phase 1.22 uses it: we lean out the repository **before**
adding new features, so the foundation is strong first. Adding features on top of
dead scaffolding and an ad-hoc test net is the "features before foundation"
headache we are deliberately avoiding. This aligns with the review docs'
"stop the bleeding before adding surface area" guidance.

## The three tool categories

Everything under `tools/` (and the leftover `tests/run.php`) falls into one of
three groups:

| Category | Examples | Action |
|---|---|---|
| **Dead one-shots** | `apply-*.php`, `add-*.php`, `export-phase-*.sh`, `phase015-*`, `tests/run.php`, `*.bak` | **Remove now** - already applied; git history preserves them |
| **Old verify test net** | `verify-*.php` (~130), `run-verification-suite.php` | **Retire incrementally** - only as Pest replacements land |
| **Live operational tools** | `diagnose-*`, `audit-*`, `repair-*`, `quarantine-*`, `send-*`, `reset-*`, mailpit scripts | **Keep** - runtime ops/diagnostics, not tests |

## What this phase removes vs. keeps

- **Removes:** `tests/run.php`, `tools/apply-*.php`, `tools/add-*.php`,
  `tools/export-phase-*.sh`, `tools/phase015-fix-composer-autoload.php`, stale
  `*.bak` files.
- **Keeps:** all `verify-*.php` (for now), `run-verification-suite.php`, and every
  operational/diagnostic script.

## Guiding rule: coverage must never go down

A `verify-*.php` script is deleted **only when an equivalent Pest test exists and
passes**. This guarantees the regression net only ever grows. Bulk-deleting the
verify scripts today would re-create the exact "no persistent regression suite"
root cause that produced the historical hotfix churn.

## Tooling

- `bin/cleanup-legacy-tooling.sh` - dry-run by default; `--force` to apply. Only
  touches the dead one-shots; explicitly leaves verify-* and ops tools alone.
- `bin/tools-inventory.php` - classifies every script into
  DELETE_NOW / MIGRATE_TO_PEST / KEEP_OPS / REVIEW and writes a progress report to
  `var/reports/tools-inventory.txt`. Run it periodically to watch the
  MIGRATE_TO_PEST count shrink as Pest coverage grows.

## Notes

- Nothing is truly lost: `git` history retains every removed script.
- See `docs/contributor/legacy-tooling-retirement-policy.md` for the precise
  bucket definitions and the retirement checklist.
