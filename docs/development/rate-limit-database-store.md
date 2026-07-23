# Database-backed Rate Limit Store

Phase 1.39g-i adds a fixed-window database-backed implementation of the rate limit store contract.

## Scope

This phase adds storage and tests only. It does not enable request middleware enforcement yet.

## Store class

```text
Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore
```

The store depends on `PDO`, records attempts into `rate_limit_buckets`, and returns a `RateLimitDecision`.

## Table shape

The reference table is documented in:

```text
database/schema/rate_limit_buckets.sql
```

Fields:

```text
id
scope
identity_hash
rule_key
window_starts_at
window_ends_at
attempts
created_at
updated_at
```

## Behaviour

- Fixed-window counting is used first because it is simple and deterministic.
- The active bucket is keyed by `scope`, `identity_hash`, `rule_key`, and `window_starts_at`.
- `recordAttempt()` creates or updates a bucket for the active window.
- Attempts at or below `maxAttempts` are allowed.
- Attempts above `maxAttempts` are denied with `retryAfterSeconds` based on the window end.
- `reset()` deletes matching buckets for a rule and identity hash.

## Safety

The store expects an opaque `identity_hash`. Callers must not pass raw passwords, tokens, session IDs, or unredacted sensitive identifiers.

## Future phases

- Register the table through the unified schema engine or module-owned schema config.
- Add cleanup tooling for expired buckets.
- Add middleware/policy wiring.
- Add route policies for admin login and other sensitive routes.
