# Phase 1.41m-o: Method Plugin Diagnostics and Invalid Config Guards

## Goal

Harden method plugin diagnostics before enabling interception of any real runtime service path.

## Added classes

- `MethodPluginException`: descriptive exception with suggestion/details fields.
- `MethodPluginValidationIssue`: one config validation issue.
- `MethodPluginValidationResult`: validation result object.
- `MethodPluginConfigValidator`: checks malformed entries, missing plugin classes, and wrong-interface plugins.

## Runtime safety

This phase still does not intercept production services. The validator and diagnostics are intended to reduce risk before plugins are wired into any real application flow.

## Verification

```bash
php8.5 tools/prove-method-plugin-diagnostics.php
php8.5 tools/audit-method-plugin-diagnostics.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginDiagnosticsTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.41p-r should add a report-only execution wrapper for a dedicated safe service path, disabled for all production services by default.
