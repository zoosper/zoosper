# UserAdminController pipeline locale persistence testing

Apply:

```bash
php -l tools/apply-user-admin-controller-pipeline-locale-persistence.php
php tools/apply-user-admin-controller-pipeline-locale-persistence.php
```

Verify:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser test:

```text
/admin/users/edit?id=1
```

Select a locale, save, reload, and confirm the selected locale remains selected.
