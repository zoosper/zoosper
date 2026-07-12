# Bootstrap provider manifest loader testing

Run:

```bash
php -l app/zoosper-core/src/Bootstrap/ServiceProviderManifestLoader.php
php -l tools/verify-bootstrap-provider-manifest-loader.php
php -l tools/run-verification-suite.php
php tools/run-verification-suite.php --output=var/reports/latest-verification.txt
```

Expected:

```text
Overall result: OK
```

If anything fails, attach:

```text
var/reports/latest-verification.txt
```
