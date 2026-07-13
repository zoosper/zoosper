# Named argument locale hotfix testing

Apply:

```bash
php -l tools/apply-user-admin-controller-named-locale-hotfix.php
php tools/apply-user-admin-controller-named-locale-hotfix.php
```

Verify:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```
