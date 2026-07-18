# Phase 1.37j — Editor.js media upload endpoint wiring

## Goal

Connect the Editor.js Image Tool upload contract to the packaged media module's validator, storage and repository.

## Scope

- Add `POST /admin/media/editorjs/upload` route.
- Add `MediaEditorJsUploadController`.
- Register controller and Editor.js media services.
- Add `X-CSRF-Token` support to central CSRF middleware.
- Add request header accessor.
- Add package/core regression coverage.

## Out of scope

- JavaScript bundle changes.
- Frontend rendering of image blocks.
- Remote URL upload/fetch.
- Automatic resizing/WebP conversion.
