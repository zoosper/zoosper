# Block JSON content model testing

Run:

```bash
php tools/verify-block-json-content-model.php
php tools/demo-block-json-renderer.php
php tools/verify-editorjs-tools.php
php tools/verify-runtime-path-safety.php
php tools/verify-service-providers.php
vendor/bin/pest app/zoosper-page/tests/Unit/Content/BlockJsonToHtmlRendererTest.php
vendor/bin/pest app/zoosper-page/tests/Unit/Service/PageRendererContentJsonTest.php
```

Expected:

```text
Block JSON sample validates.
Sample renders heading, paragraph and list HTML.
Block text is escaped before HTML is generated.
PageRenderer renders block_json pages from content_json and keeps HTML fallback behaviour.
```
