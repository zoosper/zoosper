# Phase 1.37h.7 — Tools inventory test hotfix

## Problem

The `ToolsInventoryPackageWorkflowTest` used a double-quoted needle containing `$name` when searching for the literal inventory source string:

```php
"str_starts_with($name, 'verify-')"
```

PHP interpolated `$name` inside the test string, so the search needle became invalid and `strpos()` returned `false` even though the source file was correctly ordered.

## Fix

Escape the `$` in the search needle and assert both searched positions are found before comparing their ordering.
