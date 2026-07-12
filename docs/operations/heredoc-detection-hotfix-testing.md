# Heredoc detection hotfix testing

Run:

```bash
php -l tools/apply-safe-user-admin-locale-ui.php
php tools/apply-safe-user-admin-locale-ui.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

If the apply tool still cannot find the form block, run:

```bash
php tools/diagnose-safe-user-admin-locale-ui.php
```

Browser checks:

```text
/admin/users/create
/admin/users/edit?id=1
/admin/login
/admin/pages
```
