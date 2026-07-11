# Phase 0.68 - Content Editor Adapter Foundation

Zoosper now has a modular admin content editor abstraction.

## Components

```text
ContentEditorInterface
TextareaContentEditor
EditorJsContentEditor
ContentEditorRegistry
```

## Design

The selected editor renders the `content` field, but the server still sanitises posted HTML on save. Editor.js is the preferred direction because it supports block-style JSON output, but Phase 0.68 keeps textarea as source of truth until `block_json` storage is introduced.
