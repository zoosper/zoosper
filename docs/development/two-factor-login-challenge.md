# Login-Time 2FA Challenge

## Problem

Enrolled admins were not challenged for a TOTP code at login: the session was
fully authenticated on password alone, so 2FA acted as a one-time setup nag
rather than a second factor (Sonnet Phase 2 §1). The `admin_two_factor_challenges`
table existed but nothing used it.

## Design

A challenge is a short-lived, single-use token issued AFTER a correct password but
BEFORE the session is fully authenticated:

1. Password verified for an enrolled user -> `TwoFactorChallengeService::issue()`
   returns a plaintext token; store it in a pending-2FA session and redirect to
   `/admin/2fa/challenge`.
2. The challenge page collects a TOTP code (or recovery code) and posts it with
   the token.
3. `verifyTotp()` / `verifyRecoveryCode()` validate the code, atomically consume
   the challenge, and return a result; the controller then promotes the session
   to fully authenticated.

## Guarantees

- Token stored only as SHA-256 hash.
- Single-use via `UPDATE ... WHERE consumed_at IS NULL` (race-safe).
- Configurable TTL (default 5 minutes).
- TOTP + recovery-code paths; recovery redemption marks the code used.

## Status

Phase 1.104 delivers the tested CORE (service, repository, value objects, tests).
Login/session/middleware wiring is Phase 1.105.
