# Locale placeholder position hotfix testing

Run:

```bash
php -l tools/apply-safe-user-admin-locale-ui-position-hotfix.php
php tools/apply-safe-user-admin-locale-ui-position-hotfix.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser checks:

```text
/admin/users/edit?id=1
/admin/users/create
/admin/login
/admin/pages
```

Expected:

```text
The Name input remains valid.
The Admin interface locale field appears before Email.
Login remains clean.
```
