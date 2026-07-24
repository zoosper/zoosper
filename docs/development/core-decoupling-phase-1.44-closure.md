# Phase 1.44g-z: Core Decoupling Readiness Closure

## Summary

Phase 1.44 starts the reviewer-recommended Decouple arc without a risky runtime cutover.

Completed outcomes:

1. Audited `zoosper-core` downstream feature-module coupling.
2. Wrote a concrete decoupling plan for fallback routing, site context, console kernel, and admin/API shell split.
3. Added core-owned fallback handler contract and safe null fallback implementation.
4. Added core-owned site context provider contract and safe null provider implementation.
5. Added page-module fallback adapter stub as a safe no-op proof.
6. Added site-module context provider adapter stub as a safe no-op proof.
7. Added closure audits and tests.

## Safety boundary

Runtime fallback is not rewired.
Runtime site context binding is not changed.
Existing concrete paths remain in place.
Remaining core downstream references are expected until later runtime cutover phases remove them.

## What this enables next

A later phase can replace concrete core imports one seam at a time:

- Core fallback route handler -> page module fallback adapter.
- Core site context implementation -> site module provider adapter.
- Thin console kernel -> module-owned console commands.

Each cutover should have adapter tests, wiring tests, smoke/audit tools, and a rollback path.
