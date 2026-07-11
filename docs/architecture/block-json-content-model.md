# Phase 0.76 - Block JSON Content Model Planning / Migration Foundation

Zoosper now has the first server-side contracts for structured block content.

## Added concepts

```text
ContentFormat
BlockJsonValidator
BlockJsonToHtmlRenderer
```

## Supported blocks in this foundation

```text
paragraph
header h2/h3/h4
ordered/unordered list
```

The active database format remains `html`. This phase prepares validation/rendering before any schema migration.
