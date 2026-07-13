# AdminUser field definition provider testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The verifier confirms locale is included in the AdminUser core write map and rogue/module/handler fields are not blindly written to core columns.
