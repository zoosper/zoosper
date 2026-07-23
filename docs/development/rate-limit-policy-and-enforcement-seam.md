# Rate Limit Policy and Enforcement Seam

Phase 1.39m-o introduces the policy and enforcement seam for rate limiting.

## Scope

This phase adds policy resolution and enforcer classes only. It does not register middleware globally and does not attach policies to production routes yet.

## New concepts

### RateLimitPolicy

Represents a resolved policy for a request or route. A policy can either be disabled or contain a `RateLimitRule`.

### RateLimitPolicyResolverInterface

Defines the boundary for resolving a policy from a route key or context key.

### StaticRateLimitPolicyResolver

A simple deterministic resolver for early wiring and tests.

### RateLimitEnforcer

Coordinates a resolved policy, an opaque identity hash, and a `RateLimitStoreInterface` to produce a `RateLimitDecision`.

## Why this seam matters

The store is now implemented, but directly calling it from controllers or the router would create tight coupling. The policy/enforcer seam allows later middleware to stay small:

1. build or resolve a route key;
2. resolve the policy;
3. derive an opaque identity hash;
4. call the enforcer;
5. convert the decision into pass-through or a rate-limit response.

## Safety

- Disabled policy returns an allowed decision without touching the store.
- Identity hashes must be opaque and non-empty.
- Raw IPs, tokens, session IDs, passwords, and user agents should not be persisted as identities.
- Middleware wiring is deliberately deferred to the next phase.

## Next phase

The next phase should add middleware integration in report/guarded mode first, then enable route policies only for targeted security-sensitive routes such as admin login.
