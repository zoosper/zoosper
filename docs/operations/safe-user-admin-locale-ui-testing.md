# Safe UserAdminController locale UI testing

Apply:

```bash
php -l tools/apply-safe-user-admin-locale-ui.php
php tools/apply-safe-user-admin-locale-ui.php
```

Verify:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser checks:

```text
/admin/users
/admin/users/create
/admin/users/edit?id=1
/admin/login
/admin/pages
```
