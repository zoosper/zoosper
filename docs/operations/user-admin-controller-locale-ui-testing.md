# Explicit UserAdminController locale UI testing

Run:

```bash
php -l tools/apply-user-admin-controller-locale-ui.php
php tools/apply-user-admin-controller-locale-ui.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

If the apply tool cannot patch safely:

```bash
php tools/diagnose-user-admin-controller-locale-ui.php
```

Browser checks:

```text
/admin/users
/admin/users/create
/admin/users/edit?id=1
/admin/login
/admin/pages/edit?id=1
```
