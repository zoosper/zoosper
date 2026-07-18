# Tools inventory package workflow classification

Phase 1.37h.6 classifies permanent module/package workflow tools as `KEEP_OPS` in `bin/tools-inventory.php`.

The classified tools are:

```text
tools/ensure-package-testsuites.php
tools/generate-module-composer-manifests.php
tools/normalise-package-testsuites.php
tools/pilot-extract-media-path-repository.php
tools/remove-media-app-compatibility.php
tools/sync-module-autoload.php
```

Run:

```bash
PHP=php8.5 bin/verify
```

Expected inventory outcome:

```text
REVIEW 0
```
