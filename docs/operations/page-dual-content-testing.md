# Page dual content testing

Run:

```bash
php tools/verify-page-content-format-schema.php
php tools/audit-page-content-format-data.php
php tools/verify-page-dual-content-hydration.php
php tools/diagnose-page-dual-content.php
php tools/verify-frontend-page-view-noescape.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/admin/pages/edit?id=1
/
```

Expected:

```text
Existing HTML pages still save and render.
Hydrated Page object has contentFormat=html and contentJson=null.
```
