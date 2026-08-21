# zoosper/media

Zoosper Media module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Media\` maps to `src/`.

## Architecture

- `src/Controller/`
- `src/EditorJs/`
- `src/Model/`
- `src/Processing/`
- `src/Repository/`
- `src/Service/`

## Configuration

- `config/acl.php`: ACL groups and permissions.
- `config/admin_menu.php`: Admin navigation items.
- `config/admin_routes.php`: Authenticated Admin routes.
- `config/controllers.php`: Controller factories.
- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/services.php`: Service-container bindings.

## Routes

- `GET /admin/media` from `config/admin_routes.php`.
- `GET /admin/media/upload` from `config/admin_routes.php`.
- `POST /admin/media/upload` from `config/admin_routes.php`.
- `POST /admin/media/editorjs/upload` from `config/admin_routes.php`.

## Dependencies

- `ext-pdo`: `*`.
- `php`: `^8.5`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- Development dependencies: `pestphp/pest`, `pestphp/pest-plugin`, `phpunit/phpunit`.

## Database

- Declarative schema is owned by `config/db_schema.php`.

## Extension points

- `config/acl.php` for ACL declarations.
- `config/admin_menu.php` for Admin navigation.
- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- Admin routes remain subject to authentication, ACL, and central stateful middleware such as CSRF protection.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-media/tests`.
- Discover the current regression files with `find packages/zoosper-media/tests -type f -name '*Test.php' | sort`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Phase 9IF Media lifecycle truth closure
Media assets now use POST-only, media.manage, CSRF-protected archive, restore and archived-first permanent deletion boundaries. Metadata deletion is transactional and owned-file cleanup is conservative and audited. Upload derivatives remain disabled by default; LocalCopyMediaProcessor is not image transformation support.

### Secure raster ingest
JPEG, PNG, WebP and GIF uploads are decoded and freshly re-encoded through GD before either the private original or public copy is written. This removes untrusted original byte streams and metadata from published storage. The current GIF policy stores a canonical single-frame GIF; animated-image preservation requires a future animation-aware engine. A 40-megapixel decode ceiling limits decompression risk.

### Runtime derivatives
Upload-time derivatives are enabled through `MediaUploadDerivativeDispatcher` and the replaceable `MediaProcessorInterface`. The built-in GD processor creates deterministic `thumb`, `medium`, and `large` WebP files, preserves aspect ratio, never upscales, applies centre-crop only when the source is large enough, writes atomically, and publishes matching private/public derivative files.

### Derivative records and lifecycle
Generated profiles are persisted in `media_derivatives` with dimensions, byte size, private path, and public path. Lookup is Media-owned, rows cascade with their asset, and permanent deletion removes both original and derivative files.

### Permission persistence
`media.manage` is declared by Media configuration and persisted idempotently by `database/migrations/202608140001_seed_media_permission.php`, including ACL tree metadata and the established super-admin assignment policy. Role Manager reads persisted permissions, while Media routes and Admin navigation retain the same permission code.

## Feature-owned API

Media owns stateless PAT-scoped list, detail, derivative, canonical upload, archive and restore endpoints under `/api/v1/media`. API responses expose browser-safe public paths and metadata, never private storage paths. Collection reads use bounded offset pagination (default page size `20`, maximum page size `100`, maximum page `100_000`), deterministic allow-listed sorting, and optional `q`, `status`, `mime_type`, and `extension` filters. Permanent deletion is available only for archived assets after the shared Media lifecycle verifies that neither current Pages nor restorable Page revisions reference the complete canonical public path.

## Phase 10BM-B visual Admin Grid

The Media library uses the stable `admin.media` workspace with server-side total-count pagination, allow-listed filters and sorting, persistent page-size and view state, and Media-owned responsive cards. Upload, Editor.js integration, archive, restore and reference-safe permanent deletion remain feature-owned. Media depends on `zoosper/admin-grid` and `zoosper/grid`, not the concrete Admin application module.

## Media workspace URL ownership

The Media Grid resets to `/admin/media` and intentionally hides export until Media owns a real export endpoint. No Pages route is reused by Media controls.

## Media Grid query state

Media filtering, sorting and card metadata visibility use the shared Admin Collection Grid query adapter. Filename search uses parameterised predicates for both stored and original filenames. Preview, ID and actions are mandatory Media workspace capabilities; optional metadata follows submitted visible-column state.

## Pagination ownership

This package directly consumes the stable `Zoosper\Pagination` request/result boundary through `zoosper/pagination` (`dev-dev`). It must not import `Marko\Pagination` classes.


## Phase 10AP-A Media reads and derivatives

The feature-owned `GET /api/v1/media`, `GET /api/v1/media/{id}`, and `GET /api/v1/media/{id}/derivatives` routes require the `media:read` PAT scope plus the token owner's `media.manage` permission. The collection response includes `page`, `page_size`, `page_count`, `total`, `has_previous`, and `has_next`. Invalid pagination, sort, direction, and filter values are bounded or reduced to safe defaults. Media remains globally owned because the current `media_assets` contract has no `site_id`; this phase does not invent site ownership or silently infer it from the request host. Responses expose `public_path` only and never return private `storage_path` values.


## Phase 10AP-B canonical PAT upload

The feature-owned stateless `POST /api/v1/media` route requires the `media:upload` PAT scope plus the token owner's `media.manage` permission. Multipart input is read from the immutable request upload key `file`; controllers never read `$_FILES` after `Request::fromGlobals()` captures it. The thin API adapter delegates to the same `MediaUploadService` used by Admin and Editor.js, preserving the 5 MB JPEG, PNG, GIF, and WebP allow-list, MIME and decoded-image validation, 40-megapixel canonical re-encoding ceiling, private-original/public-copy separation, enabled `thumb`, `medium`, and `large` WebP derivatives, persisted asset and derivative metadata, and failure cleanup.

A successful upload returns HTTP `201` with the public asset representation and public derivative representations. Responses never include private `storage_path` values or PAT secrets. Audit action `media.api_uploaded` records the asset ID, internal token ID, and public token ID only.
