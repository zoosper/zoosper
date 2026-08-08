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

## Phase 9FO form and request-query closure

Page Admin form context, Site options, content-editor fallback, SEO fields, structured-content fields, CSRF token and extensible `page.form` section rendering now live in a Page-owned form renderer. Page Grid reads query state from the immutable Request query map rather than `$_GET`.

## Phase 9FP final Page Admin controller closure

Pages Grid screen composition and protected Grid mutations now run through a Page-owned Grid responder. Read-only Admin preview resolution now runs through a Page-owned preview responder while retaining the single `PageRenderer` path. `PageAdminController` is reduced to authentication, entity lookup, delegation, flash/redirect selection and HTTP response selection.

## Admin dependency honesty

The Page package intentionally retains `zoosper/admin` because its Admin UI uses three shared Admin-owned contracts: `ContentEditorInterface`, `FlashMessageStoreInterface`, and the `AdminFormConfigAggregator` compatibility bridge. Page does not import concrete Admin editors, layouts, or view renderers. Removing the package dependency is deferred until those contracts and all cross-module consumers are migrated together.

## Admin package decoupling

Page now consumes shared presentation contracts from Core and no longer requires `zoosper/admin`. Concrete editor, flash-message, layout, and view implementations remain outside the Page package.

## Page Grid runtime ownership

The active Admin Grid path is `PageAdminGridResponder` with `PageGridWorkspace` and `PageGridMutationCoordinator`. Earlier complete-page builder and generic controller-adapter scaffolding has been retired.
