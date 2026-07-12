# Verification runner testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
cat var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The screen output should give a short command summary. The full verifier output should be written to the report file.
