# Phase 0.74 progress report

## Feature name

Editor.js Header/List Tools with Safe HTML Bridge.

## Implemented

- Added `@editorjs/header` dependency.
- Added `@editorjs/list` dependency.
- Exposed Header and List tools through the local Editor.js bundle.
- Registered heading/list tools in the admin runtime adapter.
- Extended HTML-to-block bridge for h2/h3/h4 and ul/ol.
- Extended block-to-HTML bridge for headings and lists.

## What remains

- Add block_json storage and validation.
- Add server-side block renderer.
- Add media/image block only after media upload security foundation.
