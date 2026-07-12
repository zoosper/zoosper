# Editor.js JSON verifier testing

Run:

```bash
php -l tools/verify-editorjs-json-save-pipeline.php
php tools/verify-editor-interface-contracts.php
php tools/verify-editorjs-json-save-pipeline.php
php tools/verify-admin-content-editor.php
php tools/verify-service-providers.php
```

Expected:

```text
Result: OK
```

Browser checks:

```text
/
/admin/pages/edit?id=1
```
