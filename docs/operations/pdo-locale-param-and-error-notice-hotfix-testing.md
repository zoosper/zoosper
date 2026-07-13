# PDO locale parameter and error notice hotfix testing

Apply:

```bash
php -l tools/apply-admin-user-locale-pdo-param-hotfix.php
php tools/apply-admin-user-locale-pdo-param-hotfix.php
php -l tools/apply-admin-notice-error-css.php
php tools/apply-admin-notice-error-css.php
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

Save a locale and confirm the form saves without the HY093 error and error notices are styled if any error does occur.
