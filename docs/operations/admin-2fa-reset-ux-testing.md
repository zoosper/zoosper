# Admin 2FA reset UX testing

## Repair schema if verifier reports missing columns

```bash
php tools/repair-two-factor-schema.php
php tools/verify-two-factor-schema.php
```

## CLI reset test

```bash
php tools/reset-admin-2fa.php --admin-user-id=1 --performed-by=1 --yes
```

## Admin UI reset test

Open:

```text
/admin/users/edit?id=1
```

Click **Reset 2FA**.

Expected redirect:

```text
/admin/users/edit?id=1&notice=2fa_reset
```

Expected notice:

```text
2FA reset completed. The admin user can enrol again on their next login.
```
