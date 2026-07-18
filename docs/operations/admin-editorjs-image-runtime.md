# Admin Editor.js Image Tool runtime operations

Run targeted tests:

```bash
vendor/bin/pest app/zoosper-admin/tests/Unit/Editor/EditorJsImageRuntimeWiringTest.php packages/zoosper-media/tests/Unit/EditorJs/EditorJsImageToolRuntimeContractTest.php
```

Refresh JavaScript dependencies and bundle when working in a browser environment:

```bash
npm install
npm run build:admin-editor
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Manual smoke:

```text
/admin/pages/create
```

Expected:

```text
Editor.js initialises with image tooling when the bundle contains ImageTool.
Async uploads are sent to /admin/media/editorjs/upload using field=image and X-CSRF-Token.
Textarea fallback remains available if the editor bundle fails to load.
```
