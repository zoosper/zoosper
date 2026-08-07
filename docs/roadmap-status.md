# Roadmap status

## Phase 9FLA-L: Media/Page closure and dependency honesty

- Media concrete Admin layout/view decoupling is complete and guarded.
- Page Admin layout/view construction uses Auth-owned interfaces and is guarded.
- Page structured `block_json` rendering with saved-HTML fallback is implemented and behaviourally tested.
- Managed Editor.js image sanitisation is composed from Media; Page now declares that runtime dependency explicitly.
- Active follow-up work is limited to remaining Page-specific concrete Admin editor/form/grid dependencies and further visible Page workspace improvements.

## Phase 9FM route-parameter cutover

Page edit, preview, publish and unpublish actions now expose constrained parameterised routes and resolve the Page identifier from immutable request route parameters. Existing query-string routes remain temporarily available for backwards compatibility, while newly generated Grid and edit-form links use canonical path URLs.

## Phase 9FN save and publication closure

Page create/update normalisation, extensible form processing, entity-save lifecycle execution and persistence now run through a Page-owned save coordinator. Single-Page publish/unpublish mutations and events run through a Page-owned publication coordinator. The Admin controller remains responsible for HTTP responses, flash presentation and redirects.

