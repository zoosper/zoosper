# Static asset publishing

## Publish one theme

```bash
php tools/publish-static-assets.php --theme=default
```

## Publish all themes

```bash
php tools/publish-static-assets.php --all
```

## Dry-run

```bash
php tools/publish-static-assets.php --theme=default --dry-run
```

## Verify

```bash
php tools/verify-static-assets.php --theme=default
php tools/diagnose-frontend-cdn-urls.php --host=zoosper.lowes.com.au --path=/about-us --theme=default
```

## Security

The publisher skips executable and sensitive-looking extensions such as PHP, shell scripts, SQL dumps, environment files and private key/certificate files.
