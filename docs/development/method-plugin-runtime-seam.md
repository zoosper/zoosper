# Phase 1.41s-u: Disabled-by-Default Method Plugin Runtime Seam

## Goal

Add a disabled-by-default integration seam around report-only method plugin execution. This brings the plugin system one step closer to runtime integration while keeping all production service paths untouched by default.

## Safety model

- `MethodPluginRuntimeConfig::disabled()` is the default.
- Disabled runtime calls the original callable directly and does not record plugin reports.
- Report-only execution requires `enabled=true`, `reportOnly=true`, and an explicit invocation allow-list key.
- The runtime seam still returns baseline output when report-only execution is enabled.

## Added classes

- `MethodPluginRuntimeConfig`
- `MethodPluginRuntime`

## Verification

```bash
php8.5 tools/prove-method-plugin-runtime-seam.php
php8.5 tools/audit-method-plugin-runtime-seam.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginRuntimeSeamTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.41v-z should close the 1.41 method plugin foundation with final drift guards, audits, and documentation before any real service path is considered.
