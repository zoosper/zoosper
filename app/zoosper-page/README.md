# zoosper/page

Zoosper_Page module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Page\` maps to `src/`.

## Architecture

- `src/Admin/`
- `src/Console/`
- `src/Content/`
- `src/Contract/`
- `src/Controller/`
- `src/Event/`
- `src/Form/`
- `src/Model/`
- `src/Repository/`
- `src/Routing/`
- `src/Sanitization/`
- `src/Save/`
- `src/Service/`

## Configuration

- `config/admin_assets.php`: Admin asset contributions.
- `config/admin_forms.php`: Admin form contributions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/admin_ui.php`: Module runtime configuration.
- `config/console.php`: Console command discovery.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/entity_save_listeners.php`: Entity-save lifecycle listeners.
- `config/grid_columns.php`: Grid column contributions.
- `config/logging.php`: Module log channel/file.
- `config/page_revisions.php`: Module runtime configuration.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/pages` from `config/admin_routes.php`.
- `POST /admin/pages/bulk-action` from `config/admin_routes.php`.
- `POST /admin/pages/grid` from `config/admin_routes.php`.
- `GET /admin/pages/export` from `config/admin_routes.php`.
- `GET /admin/pages/create` from `config/admin_routes.php`.
- `POST /admin/pages/create` from `config/admin_routes.php`.
- `GET /admin/pages/{id:\d+}/edit` from `config/admin_routes.php`.
- `POST /admin/pages/{id:\d+}/edit` from `config/admin_routes.php`.
- `GET /admin/pages/{id:\d+}/preview` from `config/admin_routes.php`.
- `GET /admin/pages/{id:\d+}/revisions/{revisionId:\d+}/preview` from `config/admin_routes.php`.
- `POST /admin/pages/{id:\d+}/revisions/{revisionId:\d+}/restore` from `config/admin_routes.php`.
- `POST /admin/pages/{id:\d+}/publish` from `config/admin_routes.php`.
- `POST /admin/pages/{id:\d+}/unpublish` from `config/admin_routes.php`.
- `GET /admin/pages/edit` from `config/admin_routes.php`.
- `POST /admin/pages/edit` from `config/admin_routes.php`.
- `GET /admin/pages/preview` from `config/admin_routes.php`.
- `POST /admin/pages/publish` from `config/admin_routes.php`.
- `POST /admin/pages/unpublish` from `config/admin_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.
- `zoosper/grid`: `dev-dev`.
- `zoosper/media`: `dev-dev`.
- `zoosper/site`: `dev-dev`.
- `zoosper/theme`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.
- Module migrations: `database/migrations/202607090004_create_page_tables.php`, `database/migrations/202608090001_expand_page_revision_snapshots.php`.

## Extension points

- `config/admin_assets.php` for Admin assets.
- `config/admin_forms.php` for form sections.
- `config/admin_menu.php` for Admin navigation.
- `config/console.php` for console commands.
- `config/entity_save_listeners.php` for save lifecycle listeners.
- `config/grid_columns.php` for Grid columns.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-page/tests`.
- Current regression files discovered: `67`. Use `find app/zoosper-page/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Lifecycle
- `PageLifecycleCoordinator` owns archive, restore-to-draft, and guarded permanent deletion.
- Archive and restore capture complete safety revisions before mutation.
- Permanent deletion requires an archived Page and refuses Menu-item or Page URL-rewrite references.
- Revision and Page deletion run in one transaction. Declarative schema repeats the migration-owned `pages.id`, `page_revisions.id`, and `page_revisions.page_id` identity columns so the merged schema can validate the `ON DELETE CASCADE` relationship without relying on live-database introspection.
- HTTP routes and destructive Admin presentation remain a following adoption slice; this phase establishes the tested domain and integrity boundary first.

### HTTP lifecycle adoption
- The Page edit screen renders contextual Archive, Restore-to-draft, and separated permanent-delete forms.
- All lifecycle mutations are POST-only, require `page.manage`, and use central CSRF validation.
- Permanent deletion remains archived-first and reference-guarded by the Page lifecycle domain.
- Lifecycle presentation contains no inline JavaScript confirmation handlers.

### Page editor compactness and revision paging
- Revision history is collapsed by default when revisions exist and renders ten rows per server-side page.
- Paging uses the `revision_page` query parameter and repository-level `LIMIT` / `OFFSET`; the full retained history is not rendered into the edit screen.
- Restore forms contain no inline JavaScript confirmation. Operators are instructed to preview before restoring, and the server still captures a safety snapshot.
- The duplicate Content-section guidance was removed. Editor-specific safety guidance remains owned by the editor component.
- Visual/HTML source switching and richer inline formatting are intentionally deferred to the dedicated editor roadmap so this usability phase does not invent a second content contract.

### Revision pagination enhancement
- Revision pagination is progressively enhanced with a same-origin fragment request, so Previous and Next replace only the revision history body.
- Normal links remain valid fallback navigation when JavaScript or the fragment request is unavailable.
- The browser URL is updated with `revision_page` without reloading the full Page edit form.

#### Frontend SEO presentation
- PageSeoResolver normalises Page metadata into an engine-neutral layout contract. Explicit Page title/description/canonical values take precedence; title falls back to the Page title.
- Derived canonicals require a valid absolute Site base URL. Non-request previews are `noindex,nofollow`.
