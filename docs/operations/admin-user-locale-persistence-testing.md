# Admin user locale persistence testing

Apply:

```bash
php -l tools/apply-admin-user-locale-persistence.php
php tools/apply-admin-user-locale-persistence.php
```

Verify:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser checks:

```text
/admin/users/edit?id=1
```

Save a locale, reload the edit form, and confirm the selected locale is preserved.
