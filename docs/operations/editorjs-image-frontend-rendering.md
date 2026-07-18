# Editor.js image frontend rendering operations

Run targeted tests:

```bash
vendor/bin/pest app/zoosper-page/tests/Unit/Content/BlockJsonToHtmlRendererImageTest.php packages/zoosper-media/tests/Unit/EditorJs/EditorJsImageBlockFrontendContractTest.php
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Browser smoke after saving an Editor.js page with a managed image block:

```text
/
```

Expected:

```text
The image block renders as a figure/img element using a /media/... URL.
Remote image URLs are ignored by the server renderer.
Existing paragraph/header/list blocks continue to render as before.
```
