# Editor.js media upload endpoint operations

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/EditorJs app/zoosper-core/tests/Unit/Http/CsrfHeaderSupportTest.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Manual browser/API smoke after login:

```text
POST /admin/media/editorjs/upload
field: image
header: X-CSRF-Token
```

Expected JSON shape:

```json
{
  "success": 1,
  "file": {
    "url": "/media/..."
  }
}
```
