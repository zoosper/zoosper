# Admin Login Pre-live Hook Closeout

This note records the final pre-live-hook criteria for Phase 1.39 admin-login rate limiting.

## Criteria before live admin middleware hook

- `rate_limit.php` defaults to disabled/report-only mode.
- `admin.login` policy exists.
- `RateLimitMiddlewareIntegration` returns no middleware when disabled.
- Report-only mode can be enabled in memory and produce middleware.
- Admin login smoke produces at least one denied decision in report-only mode.
- Denied decisions do not stop downstream execution.
- JSONL report events are written with opaque identity hashes.

## Still deferred

- Real `app/zoosper-auth/config/admin_middleware.php` mutation.
- HTTP 429 enforcement.
- Production enablement of report-only mode.
