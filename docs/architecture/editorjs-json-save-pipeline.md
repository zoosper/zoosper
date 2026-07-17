# Editor.js JSON Save and Frontend Rendering Pipeline

Zoosper captures the Editor.js document in a hidden `content_json` field while keeping the sanitised HTML fallback in `content`.

## Current save model

```text
content        = sanitised HTML fallback
content_json   = validated Editor.js block document
content_format = html for existing/editor bridge saves, or block_json for pages that should render from structured content
```

## Frontend rendering model

Phase 1.36 wires `content_json` into `PageRenderer`. Existing HTML pages continue rendering `pages.content`. Pages explicitly marked `content_format=block_json` render through `BlockJsonToHtmlRenderer` when valid `content_json` is available, with fallback to `pages.content` if the JSON is missing, invalid or renders empty output.

`BlockJsonToHtmlRenderer` generates only conservative HTML for supported blocks and escapes all user-supplied block text before returning HTML for templates to render without escaping.
