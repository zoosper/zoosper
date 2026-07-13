# Entity extension data persistence testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The verifier confirms extension fields are separated from core write data and rogue/virtual fields are ignored.
