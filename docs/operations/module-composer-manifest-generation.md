# Module composer manifest generation

Generate missing manifests:

```bash
php8.5 tools/generate-module-composer-manifests.php
```

Refresh existing generated manifests:

```bash
php8.5 tools/generate-module-composer-manifests.php --overwrite
```

Verify manifests:

```bash
php8.5 tools/verify-module-composer-manifests.php
PHP=php8.5 bin/verify
```

This is a preparation step for Composer path-repository extraction. It does not move any module out of `app/` yet.
