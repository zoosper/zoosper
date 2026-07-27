# Password Rehash-on-Login + RoleAdminController Config Wiring

## Rehash-on-login

`PasswordHasher::needsRehash()` wraps `password_needs_rehash()`.
`AuthService::authenticate()` checks it after a successful login (the only
point the plaintext password is available) and transparently upgrades the
stored hash via `AdminUserRepository::updatePassword()` if needed. Does not
run on failed logins or inactive users.

## RoleAdminController config wiring

`RoleAdminController` has accepted an optional `ConfigRepository` since Phase
1.111, but no factory ever passed one in until now. `app/zoosper-auth/config/
controllers.php` now injects it, so `aclGroups()` reads
`$config->array('acl')` (properly layered across every module) instead of
falling back to a raw single-file `require`.
