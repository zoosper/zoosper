# Phase 1.37j.1 — Editor.js upload endpoint test hotfix

## Problem

The endpoint wiring implementation was present, but the regression test used a double-quoted string containing `$_FILES['image']`.

PHP attempted to interpolate `$_FILES` inside the test string, causing a parse error before the test suite could run.

## Fix

Escape the `$` in the test search needle:

```php
"\$_FILES['image']"
```

This keeps the assertion checking the controller source for the literal upload field without triggering PHP string interpolation.
