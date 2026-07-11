# Phase 0.75 progress report

## Feature name

Runtime Path Safety / HTMLPurifier Cache Outside Public.

## Implemented

- Added `ProjectPathResolver`.
- Hardened `HtmlPurifierSanitizer` so relative cache paths resolve under project `var/`.
- Normalised HTML sanitizer cache path in the core service provider.
- Added runtime path safety verifier.
- Added public runtime directory cleaner.
- Improved public webroot scan to flag blocked directories, including empty ones.

## What remains

- Gradually move other runtime paths to `ProjectPathResolver`.
- Introduce block_json storage and server-side block renderer.
