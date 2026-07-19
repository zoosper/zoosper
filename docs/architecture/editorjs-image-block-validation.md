# Editor.js image block validation

Phase 1.37m.4 completes the save-side validation path for Editor.js image blocks.

## Problem

Browser upload succeeded and inserted an Editor.js `image` block into `content_json`, but saving the page failed with:

```text
Invalid Editor.js JSON payload: Block 1 has unsupported type: image.
```

The admin runtime, upload endpoint and frontend renderer supported image blocks, but `BlockJsonValidator` still rejected the new block type during page save.

## Validation policy

Image blocks are valid only when:

```text
- data.file is an object
- data.file.url starts with /media/
- caption is a string when present
- withBorder, withBackground and stretched are booleans when present
```

Remote URLs remain rejected.
