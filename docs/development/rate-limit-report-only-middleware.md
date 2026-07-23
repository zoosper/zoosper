# Report-only Rate Limit Middleware Adapter

Phase 1.39y-aa adds a report-only middleware adapter for the rate-limit pipeline.

## Scope

This phase is additive and non-enforcing. It does not globally register middleware and does not block any request.

The goal is to prove the route/middleware integration seam before enabling enforcement for sensitive routes such as admin login.

## New classes

### RateLimitReportEvent

A small immutable report object containing:

```text
key
identityHash
allowed
attempts
maxAttempts
retryAfterSeconds
now
```

### RateLimitReportSinkInterface

A sink boundary for consuming report events.

### InMemoryRateLimitReportSink

A simple test/debug sink that stores report events in memory.

### ReportOnlyRateLimitMiddleware

A callable adapter that:

1. accepts a `RateLimitContext` and downstream callable;
2. asks `RateLimitGuard` for a decision;
3. reports the decision to `RateLimitReportSinkInterface`;
4. always executes the downstream callable;
5. passes the decision to downstream for diagnostics if the callable accepts it.

## Safety model

- Denied rate-limit decisions do not block downstream execution in this phase.
- The report-only adapter is suitable for route/middleware smoke integration.
- Enforcement must be introduced separately and only after report-only integration is verified.
- The adapter works with opaque identity hashes only.

## Future enforcement phase

The next phase can add an enforcing adapter or a mode flag, but only for targeted route keys such as `admin.login`. Public/content routes should remain disabled unless explicitly configured.
