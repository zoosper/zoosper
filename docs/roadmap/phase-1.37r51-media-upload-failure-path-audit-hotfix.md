# Phase 1.37r.5.1 - Media upload failure-path audit hotfix

## Goal

Fix the package-local media upload failure-path audit.

## Diagnosis

The audit attempted to search source code for literal `$storedPath` text using double-quoted PHP strings:

```php
"str_starts_with($storedPath, '/media/')"
"'/public' . $storedPath"
```

PHP interpreted `$storedPath` as a runtime variable in the audit script, causing:

```text
Undefined variable $storedPath
```

and the public `/media/...` mapping check failed even though `MediaStoredFileCleanupServiceTest` already proved the runtime behaviour.

## Implemented

- Escaped `$storedPath` in the audit source-string probes.
- Added a regression assertion that the audit keeps the source variable names escaped.
- Updated package-owned failure-path operations documentation.

## Expected result

```text
php8.5 packages/zoosper-media/tools/audit-media-upload-failure-path.php
```

should return:

```text
Result: OK
```
