# Editor.js media upload permission hotfix operations

If the Image Tool reports an incorrect HTML response containing the admin Dashboard page, check whether the logged-in user has permission for the upload route.

The upload route is:

```text
POST /admin/media/editorjs/upload
```

Expected permissions after this hotfix:

```text
media.manage OR page.manage
```

Run:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/EditorJs/EditorJsMediaUploadPermissionTest.php
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Then repeat the browser upload smoke test:

```text
/admin/pages/create
```

Expected:

```text
The upload request returns JSON with success=1 and file.url, not an HTML Dashboard page.
```
