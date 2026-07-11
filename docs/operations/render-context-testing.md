# Render context testing

Run:

```bash
php tools/verify-render-context.php
php tools/diagnose-render-context.php --host=zoosper.lowes.com.au --path=/about-us --theme=default --route=frontend.page
```

Expected output should include:

```text
store_view
dynamic_sample
media_sample
static_sample
page_cache_key
```

If frontend rendering starts failing after this phase, check:

```bash
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
php -l app/zoosper-theme/src/Template/TemplateRenderer.php
php -l app/zoosper-page/src/Service/PageRenderer.php
```

This phase should not alter routes or enable page caching.
