# zoosper/install

Zoosper CMS installer module - environment checks, first admin user, seed site.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Install\` maps to `src/`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.

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
