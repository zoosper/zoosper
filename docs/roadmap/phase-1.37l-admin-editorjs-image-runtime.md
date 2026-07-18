# Phase 1.37l - Admin Editor.js Image Tool runtime wiring

## Goal

Connect the admin Editor.js browser runtime to the media package Image Tool configuration.

## Implemented

- `EditorJsContentEditor` now emits a `data-zoosper-image-tool` JSON config attribute when media image tooling is available.
- Admin services inject `EditorJsImageToolConfig` and `CsrfTokenManager` into the editor adapter.
- The admin JS runtime registers `tools.image` when `window.ZoosperEditorJsBundle.ImageTool` is available.
- The admin editor bundle source imports `@editorjs/image` and exposes `ImageTool`.
- `package.json` declares `@editorjs/image`.
- Runtime tests and documentation were added.

## Security

Async image uploads use the `X-CSRF-Token` request header and remain protected by the central admin CSRF middleware.

## Next phase

Phase 1.37m should polish the media/editor UX and browser smoke path, including any admin CSS needed for image blocks and any manual upload diagnostics found during testing.
