# Editor.js media image integration operations

Run targeted tests:

```bash
vendor/bin/pest packages/zoosper-media/tests/Unit/EditorJs
```

Run full verification:

```bash
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

This phase adds the response/config/sanitisation contracts only. The follow-up wiring phase should connect:

```text
/admin/media/editorjs/upload
```

to the existing media upload validator, storage service and repository.
