# Media upload integration readiness operations

Package-owned operations guide:

```text
packages/zoosper-media/docs/operations/media-upload-integration-readiness.md
```

Run the package-local probe:

```bash
php8.5 packages/zoosper-media/tools/probe-media-upload-integration-readiness.php
```

The probe should run without PHP warnings and should guide whether Phase 1.37r.7 uses concrete fixtures or safe substitutes.
