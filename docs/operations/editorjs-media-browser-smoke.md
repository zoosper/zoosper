# Editor.js media browser smoke checklist

Phase 1.37m validates the media/editor integration as a real admin user flow.

## 1. Admin editor initialisation

Open:

```text
/admin/pages/create
/admin/pages/edit?id=<page_id>
```

Browser checks:

```text
- Console has no JavaScript errors.
- Editor.js content area initialises.
- The Image Tool appears in the block menu.
- Textarea fallback still exists in the DOM.
```

## 2. Network upload verification

Open browser DevTools -> Network, upload a small PNG/JPG/WebP through the Image Tool, then inspect:

```text
Request URL    POST /admin/media/editorjs/upload
Form field     image
Request header X-CSRF-Token
Response       JSON success payload containing file.url
```

Expected success shape:

```json
{
  "success": 1,
  "file": {
    "url": "/media/..."
  }
}
```

## 3. Save and frontend rendering

After upload:

```text
- Save the page.
- Confirm content_json contains an image block with file.url.
- Open the frontend page.
- Confirm the rendered img src begins with /media/.
```

## 4. Failure state checks

Attempt an invalid file type such as `.txt` or `.pdf`, and a file bigger than the current upload limit.

Expected:

```text
- Upload is rejected.
- User sees a clear error from the Image Tool.
- No PHP warning or generic fatal page is shown.
- No invalid file is published under public/media.
```

## 5. Diagnostic command

Run:

```bash
php8.5 tools/diagnose-editorjs-media-browser-smoke.php
```

The command checks static/runtime prerequisites. It is not a replacement for the browser smoke test, but it catches known wiring mistakes quickly.
