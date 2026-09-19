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
- `zoosper/core`: `^0.3.1@alpha`.
- `zoosper/auth`: `^0.3.1@alpha`.
- `zoosper/scoped-config`: `^0.3.1@alpha`.

## Browser insertion boundary

Editor.js instances are registered against their own `data-zoosper-editor` wrapper through the editor-owned `ZoosperEditorBridge`. Contributors request validated block insertion through that wrapper; the bridge waits for readiness, uses the Editor.js Blocks API, synchronises `content_json` through `save()`, and fails closed when the instance, block type, structured field, or managed Media URL is invalid. The bridge is not a global current-editor singleton, so multiple editors remain isolated.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-editor/tests`.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

## Optional image-tool integration

The Editor.js adapter consumes `Zoosper\Core\Editor\EditorImageToolConfigInterface` when an installed feature provides it. Editor contains no concrete Media dependency and continues to render without image integration when the contract is unavailable.
