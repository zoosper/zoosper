# Admin notice CSS and locale persistence readiness testing

Apply the CSS fix:

```bash
php -l tools/apply-admin-notice-success-css.php
php tools/apply-admin-notice-success-css.php
```

Run verification:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser checks:

```text
/admin/users/edit?id=1
/admin/users/create
/admin/pages
```

Expected:

```text
The success notice is green again.
The admin-user locale field keeps the fixed layout.
```
