# Zoosper Page

## Runtime dependency and rendering status

- The Page package explicitly requires Core, Admin, Site, Theme, Grid and Media because its runtime configuration composes those contracts and services.
- Admin layout and view rendering use Auth-owned interfaces rather than concrete Admin layout/view implementations.
- Frontend `block_json` content is rendered through `BlockJsonToHtmlRenderer`; invalid structured content retains the established saved-HTML fallback.
- Managed Editor.js image blocks use the Media sanitizer through the declared `zoosper/media` dependency.

## Phase 9FM route-parameter cutover

Page edit, preview, publish and unpublish actions now expose constrained parameterised routes and resolve the Page identifier from immutable request route parameters. Existing query-string routes remain temporarily available for backwards compatibility, while newly generated Grid and edit-form links use canonical path URLs.

## Phase 9FN save and publication closure

Page create/update normalisation, extensible form processing, entity-save lifecycle execution and persistence now run through a Page-owned save coordinator. Single-Page publish/unpublish mutations and events run through a Page-owned publication coordinator. The Admin controller remains responsible for HTTP responses, flash presentation and redirects.

