# Phase 0.80 - Page Repository Dual Content Hydration Foundation

The `pages` table now has `content_format` and `content_json`. Phase 0.80 updates the PHP page model and repository to understand those columns while preserving current HTML behaviour.

## Current behaviour

```text
Admin save writes sanitised HTML to pages.content.
content_format remains html.
content_json remains null.
Frontend continues rendering pages.content.
```

## Future behaviour

A later phase will post Editor.js JSON, validate it server-side, store it in `content_json`, and render through the server-side block renderer.
