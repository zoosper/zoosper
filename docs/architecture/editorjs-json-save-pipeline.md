# Phase 0.82 - Editor.js JSON Hidden Field and Server-side Validation

Zoosper now captures the Editor.js document in a hidden `content_json` field while keeping the sanitised HTML fallback in `content`.

## Save model

```text
content       = sanitised HTML fallback used by current frontend rendering
content_json  = validated Editor.js block document for future block_json rendering
content_format = html for now
```

The frontend rendering switch is intentionally deferred to a later phase.
