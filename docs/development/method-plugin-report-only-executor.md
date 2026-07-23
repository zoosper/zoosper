# Phase 1.41p-r: Report-Only Method Plugin Execution Wrapper

## Goal

Introduce a report-only execution wrapper that can compare baseline method output with plugin-processed output while returning the baseline result to production code.

## Safety model

- The wrapper returns the baseline result from the original callable.
- Plugin execution only runs for explicitly allow-listed invocation keys.
- Non-allow-listed invocations are recorded as disabled and do not execute plugins.
- This phase only proves behaviour using a safe sample service.

## Added classes

- `MethodPluginReportOnlyResult`
- `MethodPluginReportSinkInterface`
- `InMemoryMethodPluginReportSink`
- `ReportOnlyMethodPluginExecutor`

## Verification

```bash
php8.5 tools/prove-method-plugin-report-only-executor.php
php8.5 tools/audit-method-plugin-report-only-executor.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginReportOnlyExecutorTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.41s-u should add a disabled-by-default integration seam for one internal sample service, with config and audit proof that production services remain untouched.
