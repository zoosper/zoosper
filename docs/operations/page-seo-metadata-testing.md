# Page SEO metadata testing

Apply schema and verify:

```bash
php tools/apply-page-seo-metadata-schema.php
php tools/verify-page-seo-metadata.php
php tools/diagnose-page-seo-metadata.php
php tools/verify-page-dual-content-hydration.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/admin/pages/create
/admin/pages/edit?id=1
```

Expected:

```text
Search engine optimisation section is visible.
SEO fields save without admin errors.
Existing content editor and frontend HTML rendering still work.
```
