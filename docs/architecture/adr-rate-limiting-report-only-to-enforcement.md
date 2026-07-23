# ADR: Rate Limiting Report-only to Enforcement Path

## Status

Accepted for Phase 1.39 closeout.

## Context

Rate limiting touches security-sensitive request flow. Admin authentication and CSRF middleware already protect the admin area, so new rate-limit wiring must avoid changing behaviour by default.

## Decision

Phase 1.39 closes with a disabled-by-default report-only hook and does not enforce HTTP 429 responses.

Enforcement will be a later phase after live report-only behaviour is observed and verified.

## Consequences

### Positive

- Safe default state.
- Integration can be tested without blocking real users.
- The rate-limit stack is ready for future enforcement.

### Negative

- Abuse prevention is not active until explicitly enabled and enforcement is added.
- Report data needs later operational review tooling.

## Follow-up

Future phases may add an enforcing adapter, HTTP 429 response handling, and explicit policy enablement for `admin.login`.
