# SessionGuard: Memoization + Pending-2FA State

## Memoization (Sonnet §3.1)

`SessionGuard::user()` resolves the AdminUser once per request and caches it.
`login()` primes the cache; `login()`/`logout()` invalidate it. Removes 3–5
redundant "who am I" DB round-trips (user row + permissions join) per admin page.

## Pending-2FA state (foundation for §1)

Two session keys now exist:

- `admin_user_id`      — set only when FULLY authenticated.
- `pending_2fa_user_id` — set after a correct password for a 2FA-enrolled user,
  BEFORE the second factor is verified.

`user()` reads only `admin_user_id`, so a pending session is unauthenticated and
`AuthenticationMiddleware` (fail-closed on null user) blocks it. The login flow
promotes pending → authenticated via `completeTwoFactorChallenge($user)` after a
valid OTP/recovery code, which also verifies the id matches (fixation defence).

## API

- `beginTwoFactorChallenge(AdminUser)` — enter pending state.
- `pendingTwoFactorUserId(): ?int` / `hasPendingTwoFactorChallenge(): bool`.
- `completeTwoFactorChallenge(AdminUser): bool` — promote if id matches.
- `clearPendingTwoFactorChallenge()`.

Consumed by the login controller + challenge controller in Phase 1.106.
