# Page content format schema testing

Apply schema:

```bash
php tools/apply-page-content-format-schema.php
```

Verify:

```bash
php tools/verify-page-content-format-schema.php
php tools/audit-page-content-format-data.php
php tools/verify-block-json-content-model.php
php tools/verify-runtime-path-safety.php
php tools/verify-service-providers.php
```

Expected:

```text
pages.content_format exists
pages.content_json exists
all existing pages default to html
```
