# Editor.js media image integration

Phase 1.37i starts the Editor.js image integration on top of the packaged `zoosper-media` module.

## Foundation classes

```text
Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory
Zoosper\Media\EditorJs\EditorJsImageToolConfig
Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer
```

## Response contract

The upload response factory emits the Image Tool response shape:

```json
{
  "success": 1,
  "file": {
    "url": "/media/example.png"
  }
}
```

## Client config contract

The client config builder centralises:

```text
upload endpoint: /admin/media/editorjs/upload
field name: image
accepted type: image/*
CSRF header: X-CSRF-Token
```

## Security stance

The image block sanitizer currently accepts only local managed media URLs beginning with `/media/`. Remote URLs are rejected until a separate fetch/proxy/sanitisation policy exists.
