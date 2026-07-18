# Admin Editor.js Image Tool runtime

Phase 1.37l wires the admin Editor.js runtime to the packaged media Image Tool configuration.

## Runtime flow

```text
EditorJsContentEditor
  -> EditorJsImageToolConfig::toArray(csrfToken)
  -> data-zoosper-image-tool JSON attribute
  -> public/assets/admin/js/zoosper-content-editor.js
  -> Editor.js tools.image config
  -> POST /admin/media/editorjs/upload
```

## CSRF

The runtime receives the current admin CSRF token from `CsrfTokenManager` and passes it to the Image Tool as an `X-CSRF-Token` request header. This keeps async image uploads inside the same central CSRF policy used by normal admin POST requests.

## Bundle source

The admin editor bundle source imports `@editorjs/image` and exposes `ImageTool` on `window.ZoosperEditorJsBundle`. After dependency installation, run the admin editor build to refresh the browser bundle.

## Fallback

If the Image Tool bundle is absent, the editor continues to initialise with the available paragraph/header/list tools and the textarea fallback remains available.
