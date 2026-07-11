# Phase 0.72 - Editor.js Initialisation with Safe Textarea Sync

Editor.js now initialises on Zoosper admin content fields when the local bundle is available.

## Safety model

Phase 0.72 keeps the existing textarea as the submitted source of truth:

```text
Editor.js blocks
  -> conservative HTML bridge
  -> textarea[name=content]
  -> existing POST flow
  -> server-side HTML sanitiser
  -> pages.content
```

`block_json` persistence is not active yet.
