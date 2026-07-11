# Phase 0.74 - Editor.js Header/List Tools with Safe HTML Bridge

Zoosper now registers Editor.js heading and list tools while still saving through the existing sanitised HTML textarea bridge.

## Supported blocks

```text
paragraph -> <p>
header h2/h3/h4 -> <h2>/<h3>/<h4>
list unordered -> <ul><li>
list ordered -> <ol><li>
```

`h1` is intentionally not offered in the page body. Page templates should control the main page heading.
