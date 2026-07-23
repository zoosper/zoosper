# Rate Limit Guard and Identity Hashing Seam

Phase 1.39p-r adds the request-facing guard layer that sits between future middleware/router wiring and the lower-level policy/enforcer/store objects.

## Scope

This phase is additive only. It does not register middleware globally and does not attach policies to production routes yet.

## New classes

### RateLimitContext

A small immutable object that carries:

```text
key
identityHash
now
```

The `key` is the route/context policy key. The `identityHash` must already be opaque and safe to persist. `now` is an integer Unix timestamp used for deterministic tests and fixed-window bucketing.

### RateLimitIdentityHasher

A helper for deriving an opaque SHA-256 identity hash from one or more caller identity parts. It avoids storing raw identity parts in the rate-limit bucket table.

### RateLimitGuard

A small orchestration object that resolves policy by context key and delegates to `RateLimitEnforcer`.

## Intended future middleware flow

1. Determine a route/context key such as `admin.login`.
2. Derive identity parts from safe request attributes.
3. Convert parts to an opaque hash using `RateLimitIdentityHasher`.
4. Build `RateLimitContext`.
5. Ask `RateLimitGuard` for a `RateLimitDecision`.
6. Future middleware converts denial into an HTTP 429 response.

## Safety

- Raw passwords, raw tokens, session IDs, and full request fingerprints must not be persisted.
- The stored identity must be an opaque hash.
- The guard stays independent from the router and response layer.
- Disabled policies continue to allow without touching the store.

## Next phase

The next phase should add a guarded middleware adapter in report-only mode so route integration can be verified before enforcement is enabled for admin login.
