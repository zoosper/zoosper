# Frontend CDN URL testing

Run:

```bash
php tools/verify-frontend-cdn-template-urls.php
php tools/diagnose-frontend-cdn-urls.php --host=zoosper.lowes.com.au --path=/about-us
```

Browser checks:

```text
/home
/about-us if page exists
View source and confirm CSS URL is generated through CDN/static config.
```

If CSS does not load, check `.env` values for:

```text
CDN_STATIC_BASE_URL
CDN_STATIC_PATH_PREFIX
APP_URL
```

For local fallback, CDN values can be omitted so URLs resolve against APP_URL.
