# Page Repository Dual Content Hydration Foundation

The `pages` table has `content_format` and `content_json`. The `Page` model and `PageRepository` hydrate those columns while preserving existing HTML page behaviour.

## Current behaviour

```text
content_format=html       -> PageRenderer renders pages.content
content_format=block_json -> PageRenderer renders pages.content_json via BlockJsonToHtmlRenderer, with pages.content fallback
```

This keeps existing HTML pages compatible while enabling structured Editor.js content to become the canonical frontend body for pages that opt into `block_json`.
