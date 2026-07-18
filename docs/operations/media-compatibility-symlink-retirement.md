# Media compatibility symlink retirement operations

Run dry-run first:

```bash
php8.5 tools/remove-media-app-compatibility.php --dry-run
```

Apply removal:

```bash
php8.5 tools/remove-media-app-compatibility.php
```

Verify package-independent discovery:

```bash
php8.5 tools/verify-media-package-independent-discovery.php
```

Regenerate autoload and run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Expected result:

```text
app/zoosper-media compatibility symlink removed: ok
media module discovered: ok
media module source is package or vendor: ok
```

Rollback before commit:

```bash
ln -s ../packages/zoosper-media app/zoosper-media
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```
