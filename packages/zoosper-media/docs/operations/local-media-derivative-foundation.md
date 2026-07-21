# Local media derivative foundation operations

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalMediaDerivativePathResolverTest.php packages/zoosper-media/tests/Unit/Processing/LocalMediaDerivativeFoundationAuditTest.php packages/zoosper-media/tests/Unit/Processing/MediaProcessingPolicyTest.php
```

Run the audit:

```bash
php8.5 packages/zoosper-media/tools/audit-local-media-derivative-foundation.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
