# zoosper/core

Zoosper_Core module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Core\` maps to `src/`.

## Architecture

- `src/App/`
- `src/Asset/`
- `src/Audit/`
- `src/Bootstrap/`
- `src/Cache/`
- `src/Composer/`
- `src/Config/`
- `src/Console/`
- `src/Container/`
- `src/Database/`
- `src/Editor/`
- `src/Entity/`
- `src/Event/`
- `src/Filesystem/`
- `src/Form/`
- `src/Fragment/`
- `src/Html/`
- `src/Http/`
- `src/I18n/`
- `src/Log/`
- `src/Message/`
- `src/Module/`
- `src/Pagination/`
- `src/Plugin/`
- `src/Release/`
- `src/Routing/`
- `src/Scaffold/`
- `src/Schema/`
- `src/Security/`
- `src/Site/`
- `src/Testing/`
- `src/Url/`
- `src/View/`

## Configuration

- `config/db_schema.php`: Declarative database schema.
- `config/logging.php`: Module log channel/file.
- `config/rate_limit.php`: Module runtime configuration.
- `config/services.php`: Service-container bindings.

## Dependencies

- `ext-pdo`: `*`.
- `marko/cache`: `^0.8`.
- `marko/cache-file`: `^0.8`.
- `marko/cache-redis`: `^0.8`.
- `marko/config`: `^0.8`.
- `marko/encryption`: `^0.8`.
- `php`: `^8.5`.
- `zoosper/errors`: `dev-dev`.

## Database

- Declarative schema is owned by `config/db_schema.php`.

## Extension points

- `config/services.php` for service bindings and interface implementations.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.

## Foreign-key schema support

The declarative schema engine supports typed foreign keys for fresh-table creation on MySQL and SQLite. Restrictive actions are the default. Existing-table reconciliation remains an explicit follow-up and is never performed as an invisible SQLite rebuild.

Foreign-key reconciliation is read-only by default: live constraints are inspected and classified, MySQL additions may be planned, and SQLite rebuild requirements are reported explicitly. No table is rebuilt automatically.

## Entity lifecycle policy

`src/Entity/Lifecycle/` provides the shared read-only decision boundary for archive, disable, and permanent-delete operations. Feature modules register one `EntityLifecyclePolicyInterface` per entity type and return descriptive blockers before any controller or executor mutates state. Policies must not perform deletion themselves. Database foreign keys remain the final integrity safety net once schema-engine support lands.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-core/tests`.
- Current regression files discovered: `181`. Use `find app/zoosper-core/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.
