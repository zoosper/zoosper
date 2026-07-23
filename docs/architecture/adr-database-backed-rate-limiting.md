# ADR: Database-backed Rate Limiting

## Status

Accepted as the Phase 1.39 direction.

## Context

Zoosper needs rate limiting that is persistent enough for admin/security-sensitive routes and does not depend only on in-memory process state.

## Decision

Use a rate limit store contract first, then add a database-backed implementation behind that contract.

The first implementation should favour simple fixed-window counters before introducing more complex algorithms.

## Rationale

A fixed-window database-backed counter is easier to reason about, test, and migrate. It gives Zoosper a durable baseline that can later be upgraded to sliding windows or token buckets if needed.

## Consequences

### Positive

- Simple storage model.
- Easy to test deterministically.
- Works across processes when backed by the database.
- Keeps future middleware independent from persistence details.

### Negative

- Fixed windows can create boundary bursts.
- Database writes need cleanup and indexing.
- Very high traffic endpoints may need a faster store later.

## Future considerations

A future adapter may use Redis or another fast store, but the database-backed implementation should remain the default portable baseline.
