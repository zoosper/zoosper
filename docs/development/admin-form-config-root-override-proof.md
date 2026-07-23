# Phase 1.40g-i: Admin Form Config Root Override Proof

## Goal

After Phase 1.40d-f added runtime migration markers and `ConfigFileLayeredLoader` references, this phase adds a proof layer for the expected config layering contract: module defaults are loaded first and root project overrides win while preserving unspecified module defaults.

## Verification commands

```bash
php8.5 tools/discover-config-file-layered-loader-contract.php
php8.5 tools/prove-admin-form-config-root-overrides.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigRootOverrideProofTest.php
php8.5 vendor/bin/pest
```

## Notes

The root override proof tool attempts to call `ConfigFileLayeredLoader` directly using safe reflection. If the exact runtime method shape cannot be inferred safely, it records that clearly and still validates the expected deterministic merge contract using temporary fixture files. This keeps the phase non-destructive while giving the next phase a precise contract-discovery report to wire a stricter runtime-only proof.
