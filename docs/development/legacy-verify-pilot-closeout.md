# Legacy Verify Pilot Closeout

Phase 1.37w pilot legacy verify migration is complete for the first five allowlisted scripts.

## Retired pilot scripts

- `tools/verify-project-structure.php`
- `tools/verify-runtime-path-safety.php`
- `tools/verify-service-provider-manifest-file.php`
- `tools/verify-module-composer-manifests.php`
- `tools/verify-roadmap-planning-docs.php`

## Pattern proven

Each script followed this sequence:

1. Add replacement Pest coverage.
2. Add read-only migration evidence tooling.
3. Keep deletion blocked while the ledger says `source-owned`.
4. Promote the script to `migrated` in `docs/development/legacy-verify-migration-status.md`.
5. Delete through `tools/remove-migrated-legacy-verify.php`.
6. Update migration/tooling tests in the same focused phase.

## Next migration batches

Future `tools/verify-*` scripts should be migrated in smaller thematic batches using the same ledger-aware workflow.
