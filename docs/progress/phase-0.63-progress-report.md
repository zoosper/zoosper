# Phase 0.63 progress report

## Feature name

HTML Sanitiser / Safe Content Rendering Foundation.

## Implemented

- Added sanitizer interface and safe HTML value object.
- Added conservative local fallback sanitizer.
- Added HTML Purifier adapter.
- Added configuration and verification tools.
- Added Composer helper to add `ezyang/htmlpurifier`.

## What remains

- Install `ezyang/htmlpurifier` through Composer in the project.
- Wire the sanitizer into page save/render flow.
- Introduce a clear storage strategy for `html`, `sanitised_html`, `markdown` or future `block_json` content.
- Integrate WYSIWYG editor after sanitisation is wired.
