# Rate Limit Middleware Integration Readiness

Phase 1.39ab-ad prepares the route/middleware integration step for the report-only rate limit adapter.

## Current state

The platform now has:

- rate limit rules and decisions;
- a database-backed fixed-window store;
- schema registration and expired-bucket cleanup tooling;
- policy resolver and enforcer seam;
- identity hashing and guard seam;
- report-only middleware adapter that never blocks downstream execution.

## Goal of this phase

This phase discovers the exact middleware registration point before wiring the report-only adapter into the real request pipeline.

It is intentionally read-only because the request pipeline has shipped security-sensitive middleware already, including authentication and CSRF guards. Any rate-limit integration must not disturb those protections.

## Desired final flow

The future report-only integration should be shaped as follows:

```text
incoming request
  -> existing middleware pipeline
  -> report-only rate limit middleware adapter
  -> existing controller/action handler
```

At this stage, denial decisions are diagnostic only:

```text
rate limit denied -> report event -> request continues
```

## Safety requirements

- Report-only mode must not block any route.
- Integration must be disabled by default until a configuration flag or explicit registration enables it.
- Admin login is the first future enforcement candidate, but not in this phase.
- The adapter must use opaque identity hashes only.
- Existing authentication, CSRF, and route-permission behaviour must continue unchanged.

## Read-only audit

Run:

```bash
php8.5 tools/audit-rate-limit-middleware-integration.php
```

The audit writes:

```text
var/reports/rate-limit-middleware-integration.txt
var/reports/rate-limit-middleware-integration.log
```

The report lists likely integration candidates such as files containing:

```text
middleware
pipeline
ModuleRouteDefinition
AuthenticationMiddleware
Csrf
```

## Next phase

After reviewing the report, the next phase should add a guarded report-only registration behind an explicit disabled-by-default configuration switch.
