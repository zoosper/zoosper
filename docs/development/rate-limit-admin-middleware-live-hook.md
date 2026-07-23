# Rate Limit Admin Middleware Live Hook

Phase 1.39bd-bj adds the final report-only admin middleware hook for Phase 1.39.

## Safety model

The hook must remain safe by default:

- `app/zoosper-core/config/rate_limit.php` keeps `enabled => false`.
- `mode` remains `report_only`.
- `admin.login` policy exists but is inactive until the config is explicitly enabled.
- Existing authentication and CSRF middleware ordering is preserved.
- Enforcement is not wired in this phase.

## Hook target

The hook target is:

```text
app/zoosper-auth/config/admin_middleware.php
```

The previous hook planner reported this file as a return-array shape containing authentication middleware, with `CAN_PATCH_LATER yes`.

## Hook shape

The guarded patch tool inserts a closure middleware entry that checks `rate_limit.php` at runtime. If rate limiting is disabled or not in report-only mode, it simply calls downstream.

When explicitly enabled, the closure reports through the existing report-only scaffolding. It must not produce HTTP 429 responses.

## Deferred work

The following is deliberately deferred beyond Phase 1.39:

- enforcing HTTP 429 responses;
- enabling report-only mode by default;
- adding admin UI controls;
- adding per-tenant/site rate-limit policy editing.
