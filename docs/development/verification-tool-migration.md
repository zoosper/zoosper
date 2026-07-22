# Verification Tool Migration Policy

Zoosper historically used many `tools/verify-*` scripts as source-level regression checks before the Pest suite became broad enough to own those contracts directly.

As of Phase 1.37w, source contracts should move into Pest tests whenever possible.

## Categories

### MIGRATE_TO_PEST

Use this category for legacy `tools/verify-*` scripts that only assert code, config, docs, schema, route, service, or wiring contracts.

These checks should become durable Pest tests near the module that owns the behaviour.

### KEEP_OPS

Use this category for scripts that are useful operational commands, source inspectors, diagnostics, repair helpers, smoke tools, publishing tools, cleanup tools, or composer/package workflow commands.

Examples include scripts named with prefixes such as:

- `audit-*`
- `diagnose-*`
- `inspect-*`
- `repair-*`
- `smoke-*`
- `clean-*`
- `publish-*`
- `sync-*`
- `generate-*`
- `normalise-*`
- `ensure-*`

### DELETE_NOW

Use this category only for files that are confirmed obsolete, unsafe, and not needed for migration history.

### REVIEW

Use this category when ownership is unclear.

## Rule of thumb

If the tool proves the repository is correct, move it to Pest.

If the tool helps an operator inspect, diagnose, repair, publish, generate, sync, or smoke-test something, keep it in `tools/`.

## Safe migration sequence

1. Add or locate equivalent Pest coverage for the source contract.
2. Run the full verification suite.
3. Remove the legacy `tools/verify-*` script only after the Pest test owns the same contract.
4. Re-run the tools inventory and confirm the script no longer appears under `MIGRATE_TO_PEST`.

## Commit hygiene

Generated reports under `var/reports/` are runtime artefacts unless explicitly promoted to documentation. Prefer committing tests, docs, and durable tooling only.
