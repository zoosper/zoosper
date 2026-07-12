# UserAdminController locale UI parse hotfix testing

Run:

```bash
php -l tools/apply-user-admin-controller-locale-ui-parse-hotfix.php
php tools/apply-user-admin-controller-locale-ui-parse-hotfix.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Browser checks:

```text
/admin/pages
/admin/users
/admin/login
```

Expected:

```text
No parse error.
UserAdminController syntax check passes.
Admin pages load again.
```
