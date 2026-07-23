# Rate Limit Admin Middleware Hook Strategy

Phase 1.39ao-as prepares the real admin middleware hook for report-only rate limiting.

## Current state

The platform now has:

- rate-limit rules, decisions, and store contract;
- database-backed fixed-window store;
- schema registration and cleanup tooling;
- policy resolver, enforcer, identity hasher, and guard;
- report-only middleware adapter;
- disabled-by-default runtime config;
- file-backed report sink;
- disabled-by-default middleware integration provider.

## Candidate hook from readiness audit

The integration readiness audit identified many candidate files. The strongest immediate target for admin pipeline integration is:

```text
app/zoosper-auth/config/admin_middleware.php
```

because it is explicitly an admin middleware config file and was reported as containing middleware, `AuthenticationMiddleware`, and CSRF signals.

Other important surrounding files include:

```text
app/zoosper-core/src/Http/Middleware/ModuleAdminMiddlewareLoader.php
app/zoosper-core/src/Http/Middleware/MiddlewarePipeline.php
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
```

## Strategy

Do not directly mutate the live middleware configuration until the exact source shape is discovered.

This phase therefore adds:

1. a discovery tool that snapshots the candidate hook files;
2. a patch planner that reports whether a safe guarded insertion pattern exists;
3. an audit proving the hook tooling is ready.

## Required safety behaviour

- Default `rate_limit.php` remains disabled.
- Report-only adapter must never block downstream execution.
- No enforcement mode is wired in this phase.
- Existing authentication, CSRF, and route permission behaviour must remain unchanged.

## Next phase

After reviewing the generated hook report and plan, the next phase can add the real disabled-by-default report-only middleware registration to the admin middleware configuration or module middleware loader, depending on the exact source pattern discovered.
