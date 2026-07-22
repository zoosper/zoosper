# Local copy derivative smoke operations

Run the static audit:

```bash
php8.5 packages/zoosper-media/tools/audit-local-copy-derivative-smoke.php
```

Run the controlled smoke:

```bash
php8.5 packages/zoosper-media/tools/smoke-local-copy-derivative-generation.php
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorInterfaceCompatibilityTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorPolicyCompatibilityTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorProfileCompatibilityTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyDerivativeSmokeDiagnosticsTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyDerivativeSmokeToolTest.php packages/zoosper-media/tests/Unit/Processing/MediaUploadDerivativeSeamTest.php packages/zoosper-media/tests/Unit/Processing/LocalCopyMediaProcessorAuditTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

The smoke path writes only under `var/smoke/media-derivatives`. Do not commit generated smoke output.
