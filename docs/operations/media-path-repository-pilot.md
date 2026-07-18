# Media path repository pilot operations

Run a dry-run first:

```bash
php8.5 tools/pilot-extract-media-path-repository.php --dry-run
```

Apply the pilot:

```bash
php8.5 tools/pilot-extract-media-path-repository.php
PHP=php8.5 composer update zoosper/media --with-dependencies
PHP=php8.5 composer dump-autoload
php8.5 tools/verify-media-path-repository-pilot.php
PHP=php8.5 bin/verify
```

## Expected result

```text
packages/zoosper-media exists
app/zoosper-media remains available as a compatibility symlink
root composer.json contains path repository packages/zoosper-media
root composer.json requires zoosper/media *@dev
full verification remains green
```

## Rollback

If needed before committing:

```bash
rm app/zoosper-media
mv packages/zoosper-media app/zoosper-media
```

Then remove the `zoosper/media` require and `packages/zoosper-media` path repository from root composer.json, followed by:

```bash
PHP=php8.5 composer update
PHP=php8.5 composer dump-autoload
```
