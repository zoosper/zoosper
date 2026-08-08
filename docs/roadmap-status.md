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

## Phase 9FO form and request-query closure

Page Admin form context, Site options, content-editor fallback, SEO fields, structured-content fields, CSRF token and extensible `page.form` section rendering now live in a Page-owned form renderer. Page Grid reads query state from the immutable Request query map rather than `$_GET`.

## Phase 9FP final Page Admin controller closure

Pages Grid screen composition and protected Grid mutations now run through a Page-owned Grid responder. Read-only Admin preview resolution now runs through a Page-owned preview responder while retaining the single `PageRenderer` path. `PageAdminController` is reduced to authentication, entity lookup, delegation, flash/redirect selection and HTTP response selection.

## Phase 9FQ dependency-honesty closure

Page package decoupling is closed at the honest compatibility boundary. The package keeps `zoosper/admin` while three Admin-owned shared contracts remain in active use. Runtime ownership guards prevent expansion beyond that allow-list. Contract relocation remains a separate cross-module compatibility project rather than unfinished Page controller work.

## Phase 9FR shared presentation-contract migration

The cross-module presentation-contract migration is complete: shared contracts moved to Core, all consumers and service registrations use the Core namespaces, obsolete Admin-owned contract files were retired, and Page/Settings removed their Admin Composer dependency.

## Phase 9FS dead-runtime and compatibility closure

Post-migration cleanup removed three unused Page Grid integration abstractions and their isolated tests. Core presentation contracts, Admin concrete implementations, Page Grid runtime composition, export, saved views, mutations, bulk actions and frontend boot remain regression-guarded.
## Phase 9FT Settings Admin runtime closure

Settings screen composition moved to `SettingsCatalogueResponder`; save and inherited-value restoration moved to `SettingsMutationCoordinator`; canonical Settings URLs are shared through `SettingsAdminUrls`. The controller retains only authentication, delegation and response return.
