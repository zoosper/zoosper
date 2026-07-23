# Rate Limiting Foundation

Phase 1.39 introduces the platform foundation for rate limiting.

## Scope of this phase

This phase adds contracts, value objects, documentation, and a read-only audit. It intentionally does not enforce limits in the request pipeline yet.

## Goals

- Define a core rate limit rule object.
- Define a core rate limit decision object.
- Define a store contract that can be backed by a database implementation.
- Keep enforcement separate from storage so middleware/controller integration can be added safely in later phases.

## Proposed storage model

A future database-backed store should be able to persist counters using fields equivalent to:

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

## Intended flow

1. A caller builds a `RateLimitRule`.
2. The enforcement layer hashes the caller identity into an opaque `identity_hash`.
3. The store increments the matching bucket for the active window.
4. The store returns a `RateLimitDecision`.
5. Future middleware converts the decision into allow/deny behaviour.

## Safety requirements

- Do not store raw passwords, tokens, session identifiers, or full IP/user-agent values as identities.
- Store only opaque hashes for identities.
- A failed store should default to a documented fail-open or fail-closed policy per route class.
- Admin login and credential-sensitive endpoints should support stricter policy than normal browsing routes.

## Non-goals

- No middleware enforcement in this phase.
- No admin UI in this phase.
- No deletion/cleanup job in this phase.
- No route policy wiring in this phase.

## Next phases

- Add database schema and store implementation.
- Add cleanup command for expired buckets.
- Add middleware/policy seam.
- Add route-level policies for admin login and sensitive endpoints.
