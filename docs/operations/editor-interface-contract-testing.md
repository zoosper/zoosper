# Editor interface contract testing

Run:

```bash
php -l app/zoosper-admin/src/Editor/EditorJsContentEditor.php
php -l tools/verify-editor-interface-contracts.php
php tools/verify-editor-interface-contracts.php
php tools/verify-editorjs-json-save-pipeline.php
php tools/verify-admin-content-editor.php
php tools/verify-service-providers.php
```

Browser checks:

```text
/
/admin/pages/edit?id=1
```

Expected:

```text
No PHP fatal error.
Editor.js still loads.
content_json hidden field is still present.
```
