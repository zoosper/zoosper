# Media schema runtime diagnostics

If Editor.js image upload returns a 500 response and `var/log/exception.log` contains:

```text
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'zoosper.media_assets' doesn't exist
```

then the code is wired, but the database has not applied the media module schema yet.

Run:

```bash
PHP=php8.5 bin/zoosper migrate
php8.5 tools/diagnose-media-schema-runtime.php
PHP=php8.5 bin/verify
```

Then repeat the browser smoke upload from:

```text
/admin/pages/create
```

Expected result:

```text
POST /admin/media/editorjs/upload returns JSON success=1 with file.url.
```

This diagnostic is intentionally separate from `bin/verify`: schema validation proves declarations are valid, while this runtime check proves the live database has the `media_assets` table.
