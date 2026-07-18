# Editor.js media upload endpoint

Phase 1.37j wires the packaged media module to expose the async Editor.js image upload endpoint:

```text
POST /admin/media/editorjs/upload
```

The endpoint is protected by the normal admin middleware pipeline and requires `media.manage`.

## Upload field

The Editor.js image config uses:

```text
field: image
```

The controller therefore reads:

```text
$_FILES['image']
```

## Response contract

Successful uploads return the Image Tool payload:

```json
{
  "success": 1,
  "file": {
    "url": "/media/2026/07/example.png"
  }
}
```

## CSRF

The central CSRF middleware now accepts the `X-CSRF-Token` request header in addition to the `_csrf_token` form field. This lets async Editor.js uploads remain CSRF-protected without weakening admin security.
