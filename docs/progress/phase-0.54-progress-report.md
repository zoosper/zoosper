# Phase 0.54 progress report

## Feature name

Render context wiring.

## Implemented

- Registered site context, CDN resolver and cache key services in `ApplicationFactory`.
- Added `TemplateViewContextProvider`.
- Updated `TemplateRenderer` to merge shared view context into template data.
- Updated `PageRenderer` to pass current site context when available.
- Added render context verification and diagnostics tools.

## What remains

- Update frontend templates to use `$cdn` for static/media URLs.
- Add WYSIWYG integration after render context is verified.
- Add real page/block cache storage later.
- Add CDN provider purge adapter later.

## Risks or considerations

- Templates now receive helper objects. Avoid using them for sensitive runtime state.
- `CurrentSiteContext` remains request-scoped. Revisit if Zoosper later uses long-running workers.
- This phase does not make admin or frontend responses shared-cacheable.
