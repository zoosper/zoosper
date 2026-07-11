# Block JSON content model testing

Run:

```bash
php tools/verify-block-json-content-model.php
php tools/demo-block-json-renderer.php
php tools/verify-editorjs-tools.php
php tools/verify-runtime-path-safety.php
php tools/verify-service-providers.php
```

Expected:

```text
Block JSON sample validates.
Sample renders heading, paragraph and list HTML.
Existing Editor.js HTML bridge remains unchanged.
```
