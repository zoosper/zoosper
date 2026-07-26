# Constant-Time Authentication

## Rule

`AuthService::authenticate()` must spend the same time whether or not the supplied
email belongs to a real, active admin. Otherwise attackers can enumerate valid
admin emails by measuring response times.

## How

- Look up the user by email.
- Choose a hash to verify:
  - the real user's `passwordHash` when the user exists, is active, and has a
    non-empty hash;
  - otherwise a cached dummy hash generated once via the real `PasswordHasher`.
- ALWAYS call `hasher->verify($password, $hashToCheck)`.
- Only after that, fail closed for missing/inactive users or invalid passwords.

The dummy hash is produced with the real hasher so its cost matches a genuine
verification (a malformed hardcoded hash would short-circuit and break the
guarantee).

## Non-goals

This does not add rate limiting or lockout (tracked separately). It only removes
the timing side-channel from the verification path.
