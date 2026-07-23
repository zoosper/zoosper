# Phase 1.40j-k: Exact Runtime Admin Config Root Override Proof

## Goal

Replace the fallback-oriented Phase 1.40g-i proof with a stricter runtime proof that calls `ConfigFileLayeredLoader::load($sources)` directly.

## Verification

```bash
php8.5 tools/discover-config-file-layered-loader-contract.php
php8.5 tools/prove-admin-form-config-root-overrides.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigExactRuntimeProofTest.php
php8.5 vendor/bin/pest
```

## Expected report

The ideal report contains:

```text
Runtime proof used: yes
Fallback proof used: no
Root override proved: yes
Errors: 0
```

If the exact `load($sources)` source shape cannot be inferred, the tool writes all attempted source variants and exits non-zero. That failure is intentionally useful because it prevents silently accepting fallback behaviour once the real loader contract is known.
