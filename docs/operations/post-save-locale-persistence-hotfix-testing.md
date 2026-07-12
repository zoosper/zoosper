# Post-save admin user locale persistence hotfix testing

Run:

```bash
php -l tools/apply-admin-user-locale-persistence.php
php tools/apply-admin-user-locale-persistence.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser test:

```text
/admin/users/edit?id=1
```

Save the locale, reload, and confirm the selected locale is preserved.
