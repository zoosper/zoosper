# Admin entity save pipeline foundation testing

Run:

```bash
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

The verification confirms that rogue submitted fields do not enter the core write map and module fields are separated as extension data.
