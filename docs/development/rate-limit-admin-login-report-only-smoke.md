# Admin Login Report-only Rate Limit Smoke

Phase 1.39ay-bc verifies the rate-limit stack in report-only mode before mutating the live admin middleware configuration.

## Why this phase exists

The admin middleware hook planner reported that `app/zoosper-auth/config/admin_middleware.php` has a suitable return-array pattern with authentication middleware and can be patched later. Before patching that live configuration, this phase proves the configured admin login policy can flow through the actual rate-limit classes in a deterministic command-line smoke test.

## What the smoke proves

The smoke command verifies:

- `rate_limit.php` still defaults to disabled/report-only mode;
- `admin.login` policy exists;
- a runtime copy can be enabled in memory without changing config files;
- `RateLimitMiddlewareIntegration` returns report-only middleware when enabled;
- `DatabaseRateLimitStore` records attempts;
- a denied decision can be produced;
- `ReportOnlyRateLimitMiddleware` still executes downstream after denial;
- `FileRateLimitReportSink` writes JSONL events.

## Command

```bash
php8.5 tools/smoke-rate-limit-admin-login-report-only.php
```

## Output

The command writes:

```text
var/reports/rate-limit-admin-login-smoke.txt
var/reports/rate-limit-admin-login-smoke.log
var/reports/rate-limit-admin-login-smoke-events.jsonl
```

## Safety

The smoke command intentionally forces `enabled=true` only in an in-memory copy of the configuration. It does not change `app/zoosper-core/config/rate_limit.php`.

## Next phase

If this smoke is green, the next phase can patch the live admin middleware configuration behind the disabled-by-default runtime switch.
