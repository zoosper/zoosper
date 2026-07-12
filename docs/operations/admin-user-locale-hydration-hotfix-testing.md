# Admin user locale hydration hotfix testing

Run:

```bash
php -l tools/apply-admin-user-locale-hydration.php
php tools/apply-admin-user-locale-hydration.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

If patching still fails, run:

```bash
php tools/diagnose-admin-user-locale-hydration.php
```

and share the output.
