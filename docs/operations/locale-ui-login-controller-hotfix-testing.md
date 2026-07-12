# Locale UI login controller hotfix testing

Run:

```bash
php -l tools/apply-admin-user-locale-preference-ui-hotfix.php
php tools/apply-admin-user-locale-preference-ui-hotfix.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser checks:

```text
/admin/pages/edit?id=1
/admin/login
/admin/users
```

Expected:

```text
No parse error.
Login page loads again.
Admin page edit loads again.
```
