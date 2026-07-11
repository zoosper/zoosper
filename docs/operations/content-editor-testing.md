# Content editor testing

Run:

```bash
php tools/verify-admin-content-editor.php
php tools/diagnose-admin-content-editor.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/admin/pages/create
/admin/pages/edit?id=1
```

Expected:

```text
Content field renders through editor adapter.
Textarea fallback remains usable.
Page save still sanitises HTML and shows success message.
```
