# Phase 0.77 - Page Content Format Schema / Repository Foundation

Zoosper now has an additive schema path for dual content storage:

```text
pages.content          existing sanitised HTML
pages.content_format   html, block_json or markdown
pages.content_json     future structured block JSON document
```

The current application still saves and renders HTML. This phase prepares the database for future `block_json` persistence without breaking existing pages.
