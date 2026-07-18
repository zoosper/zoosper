# Phase 1.37k - Editor.js image frontend rendering

## Goal

Render managed Editor.js image blocks from `content_json` through the frontend page renderer.

## Implemented

- Extended `BlockJsonToHtmlRenderer` to recognise `image` blocks.
- Injected `EditorJsImageBlockSanitizer` into the page block renderer when the media package is installed.
- Registered `EditorJsImageBlockSanitizer` in the media package services.
- Added tests proving managed `/media/` URLs render and remote URLs are ignored.
- Added documentation for the image rendering contract.

## Guardrails

- Existing HTML fallback pages are unchanged.
- Image blocks are rendered only when the media sanitizer accepts the URL.
- Remote URLs are not rendered yet.
- Captions and attributes are escaped before output.

## Next phase

Phase 1.37l should wire the admin Editor.js runtime so the Image Tool receives the configured upload endpoint and CSRF header from the media package.
