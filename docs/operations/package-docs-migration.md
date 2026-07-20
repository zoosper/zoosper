# Package docs migration operations

Package-owned documentation should move package-specific implementation details closer to package code while keeping root docs as indexes and links.

Run the durable package documentation audit:

```bash
php8.5 tools/audit-doc-package-ownership.php
```

The audit reports:

```text
- whether package docs folders exist
- whether the media package docs index exists
- which media-related docs still remain under root docs
```

## Manual migration workflow

For each package-specific document:

```text
1. Move detailed content to packages/<vendor-module>/docs/architecture or packages/<vendor-module>/docs/operations.
2. Leave a short root doc/index page only when the future documentation website needs a root-level navigation entry.
3. Keep roadmap/status summaries in root docs.
4. Run the package docs audit again.
5. Run full verification.
```

## Repo hygiene

Do not keep generated migration plans in the repo unless they are intentionally promoted to durable documentation.

If the temporary planner exists locally, remove it before committing:

```bash
rm -f tools/plan-package-docs-migration.php package-docs-migration-plan.txt
```

Then run:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```
