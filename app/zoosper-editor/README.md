# zoosper/editor

Zoosper_Editor module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Editor\` maps to `src/`.
- Provides decoupled content editor adapters and fallback selection for admin CMS authoring.
- Implements `ContentEditorRegistry` and `ContentEditorInterface` resolutions.
- Provides Editor.js structured block integration and textarea fallback.

## Architecture

- `src/`: Content editor implementations (`EditorJsContentEditor`, `TextareaContentEditor`, `ContentEditorRegistry`).
- `src/Config/`: Scoped runtime configuration (`ContentEditorRuntimeConfig`, `ContentEditorRuntimeConfigFactory`).

## Configuration

- `config/admin_assets.php`: Content editor script and stylesheet asset declarations.
- `config/services.php`: Service container bindings for `ContentEditorInterface`, `ContentEditorRegistry`, and runtime config factories.

## Dependencies

- `php`: `^8.5`.
- `zoosper/core`: `dev-dev`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/scoped-config`: `dev-dev`.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-editor/tests`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
