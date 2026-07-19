# Media processing policy operations

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/Processing/MediaProcessingPolicyTest.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

This phase does not generate thumbnails or WebP files yet. It defines the policy and contracts for safe follow-up implementation.

When a real processor is added later, keep these browser smoke checks from Phase 1.37m:

```text
- Upload still returns JSON success=1.
- Original remains available under storage/media/original.
- Derived files are generated outside the original path.
- Frontend image rendering continues to use managed media URLs.
```
