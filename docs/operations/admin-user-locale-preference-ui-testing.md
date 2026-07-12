# Admin user locale preference UI testing

Apply and patch:

```bash
php -l tools/apply-admin-user-locale-preference-ui.php
php tools/apply-admin-user-locale-preference-ui.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

If patching cannot safely detect the form, run:

```bash
php tools/diagnose-admin-user-locale-preference-ui.php
```

and share the output.
