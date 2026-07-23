# Rate Limit Report-only Wiring Strategy

Phase 1.39aj-an introduces a disabled-by-default wiring scaffold for report-only rate limiting.

## Why this phase exists

The platform now has rate-limit runtime config, report-only middleware, a guard, identity hashing, policy resolution, an enforcer, a database-backed store, and a file-backed report sink.

The readiness audit identified several security-sensitive integration candidates such as the middleware pipeline, module admin middleware loader, application factory, and authentication middleware configuration. Directly modifying those files should be the next step only after a factory/provider scaffold is tested.

## Design

This phase adds two small classes:

```text
RateLimitReportOnlyMiddlewareFactory
RateLimitMiddlewareIntegration
```

The factory builds a report-only middleware instance from explicit dependencies.

Concrete middleware class: `ReportOnlyRateLimitMiddleware`.

The integration provider is intentionally conservative:

- disabled config returns an empty middleware list;
- enabled config in `report_only` mode returns one report-only adapter;
- `enforce` mode returns no middleware in this phase because enforcement needs a separate adapter and explicit tests.

## Future pipeline wiring

The next phase can safely patch the real admin middleware registration path to call the provider. Because the provider returns an empty list by default, adding the call should not alter runtime behaviour until `rate_limit.php` explicitly enables it.

## Safety rules

- No route is limited by default.
- Enforce mode is not wired by this provider.
- Report-only decisions do not block downstream execution.
- The stored identity remains an opaque hash.
- Existing AuthenticationMiddleware and CsrfMiddleware behaviour must remain unchanged.
