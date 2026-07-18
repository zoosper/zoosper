# Phase 1.37i — Editor.js media image contracts

## Goal

Start Editor.js image block integration using the packaged media module without immediately coupling editor UI, upload controllers and renderers in one large change.

## Scope

- Add Editor.js image upload response factory.
- Add Editor.js image tool configuration builder with CSRF header support.
- Add image block data sanitizer for managed `/media/` URLs.
- Add package-level Pest coverage.
- Document the integration contract.

## Out of scope

- Actual `/admin/media/editorjs/upload` route wiring.
- JavaScript asset build changes.
- Remote URL fetching.
- Automatic resizing or WebP conversion.

## Next phase

Wire the admin upload endpoint to the media validator, storage service and repository, returning the response payload from `EditorJsImageUploadResponseFactory`.
