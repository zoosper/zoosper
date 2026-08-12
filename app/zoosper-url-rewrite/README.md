# zoosper/url-rewrite

Zoosper_UrlRewrite module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\UrlRewrite\` maps to `src/`.

## Architecture

- `src/Model/`
- `src/Repository/`
- `src/Service/`

## Configuration

- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.

## Testing

- Full repository suite: `zcomposer test`.
- No package-local `*Test.php` files were discovered; rely on the full repository suite and cross-package architecture tests.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Redirect management foundation
- `RedirectPolicy` normalises sources and targets, allows 301/302 only, and rejects unsafe schemes, direct loops, Site-root sources, and reserved application paths.
- `UrlRewriteRepository` supports deterministic Site-scoped listing and insert/update persistence with branch-specific PDO parameters.
- Frontend resolution and guarded Admin lifecycle remain the next adoption slice.
