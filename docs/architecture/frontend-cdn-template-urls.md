# Phase 0.58 - Frontend template CDN/static/media URL usage

## Goal

Use the render context added in earlier phases inside real frontend templates.

## What changed

- The default frontend layout now uses `$cdn->staticAsset()` for CSS.
- The default frontend layout now uses `$cdn->dynamicForContext()` for the home link.
- Page templates keep server-rendering title and body content for SEO.

## Non-goals

- No full-page cache storage.
- No AJAX fragment routes yet.
- No WYSIWYG editor yet.
- No CDN provider purge integration yet.

## Rule

SEO-critical content stays server-rendered. AJAX should be used later only for private/dynamic fragments such as cart counts, customer state, admin counters or dashboard widgets.
