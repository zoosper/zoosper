# Admin Password Policy

## Rule

New admin-user passwords, and any password change on an existing user, must
satisfy `PasswordPolicy`: minimum 12 characters and at least 2 of
{lowercase, uppercase, digit, symbol} character classes. Validation happens
BEFORE any database write.

## Where it is enforced

`UserAdminController::create()` and `UserAdminController::update()` (only when a
non-empty new password is submitted; leaving it blank on edit still means "keep
the existing password").

## Extensibility

`PasswordPolicy` takes `minLength`/`minCharacterClasses` via its constructor. The
controller accepts an optional `?PasswordPolicy $passwordPolicy = null` and falls
back to sane defaults, so tightening the policy later (e.g. from
`config/security.php`) requires only injecting a configured instance — no
behavioural change to the class itself.

## Explicitly out of scope here

`password_needs_rehash()` upgrade path (needs `PasswordHasher` internals) and
config-driven thresholds (needs the current DI wiring for this controller).
