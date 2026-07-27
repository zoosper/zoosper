# Login-Time 2FA Enforcement

Enrolled admins must supply a TOTP or recovery code at login before the session
becomes authenticated (fixes Sonnet Phase 2 §1, where 2FA was a setup nag only).

## Path

password OK →
  enrolled?  → pending-2FA session + issued challenge → /admin/2fa/challenge
             → valid code → completeTwoFactorChallenge() → /admin
  not enrolled → full login → redirect service → /admin (or /admin/2fa/setup)

## Why the challenge route is public

While pending-2FA, `SessionGuard::user()` is null, so the auth guard would send
the user to /admin/login. The route is therefore marked `public`, and the
controller enforces that a pending-2FA session exists (else it redirects to
login). This mirrors how the login route itself is public.

## Fail-open on partial deploy

If the enrollment/challenge services are not registered, login falls back to the
prior password-only behaviour — no lockout risk.

## Session keys

- `admin_user_id` — set only when fully authenticated.
- `pending_2fa_user_id` — set after password, before the second factor.
- `pending_2fa_challenge_token` — the single-use challenge token (plaintext held
  only in the session; only its SHA-256 hash is persisted).
