# Legacy Verify Pest Migration Pilot

Phase 1.37w introduced the policy for moving legacy `tools/verify-*` scripts into durable Pest tests.

Phase 1.37w.2 defines the first controlled pilot batch. The goal is to make migration deliberate and reviewable instead of bulk-deleting historical verification scripts.

## Pilot batch

The first pilot batch is intentionally limited to low-risk source-contract verification scripts:

1. `tools/verify-project-structure.php`
2. `tools/verify-runtime-path-safety.php`
3. `tools/verify-service-provider-manifest-file.php`
4. `tools/verify-module-composer-manifests.php`
5. `tools/verify-roadmap-planning-docs.php`

## Migration rule

A legacy verify script may be removed only after equivalent Pest coverage exists in the owning module test suite.

## Pilot acceptance criteria

Before deleting any script in the pilot batch:

- Identify the behaviour or source contract asserted by the legacy script.
- Add or confirm equivalent Pest coverage near the owning module.
- Run the full Pest suite.
- Remove only the migrated legacy verify script.
- Re-run the tools inventory and confirm the candidate count changes as expected.
- Keep operational scripts such as `audit-*`, `diagnose-*`, `inspect-*`, `repair-*`, `smoke-*`, `sync-*`, `publish-*`, `generate-*`, `normalise-*`, and `ensure-*` in `tools/`.

## Why this stays conservative

The current inventory reports many migration candidates, but each candidate may still hold useful historical intent. The migration should preserve that intent as durable Pest coverage rather than simply reducing file count.

## Next phase candidate

After this pilot is green, the next phase can migrate one or two scripts from this batch by reading their source, creating equivalent Pest assertions, and deleting only the migrated scripts.
