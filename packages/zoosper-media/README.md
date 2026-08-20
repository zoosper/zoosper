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
- Current regression files discovered: `26`. Use `find packages/zoosper-media/tests -type f -name '*Test.php' | sort` for the live list.
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

Media owns stateless PAT-scoped list, detail, derivative, canonical upload, archive and restore endpoints under `/api/v1/media`. API responses expose browser-safe public paths and metadata, never private storage paths. Permanent deletion is available only for archived assets after the shared Media lifecycle verifies that neither current Pages nor restorable Page revisions reference the complete canonical public path.

## Phase 10BM-B visual Admin Grid

The Media library uses the stable `admin.media` workspace with server-side total-count pagination, allow-listed filters and sorting, persistent page-size and view state, and Media-owned responsive cards. Upload, Editor.js integration, archive, restore and reference-safe permanent deletion remain feature-owned. Media depends on `zoosper/admin-grid` and `zoosper/grid`, not the concrete Admin application module.

## Media workspace URL ownership

The Media Grid resets to `/admin/media` and intentionally hides export until Media owns a real export endpoint. No Pages route is reused by Media controls.
