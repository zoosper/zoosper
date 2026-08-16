# zoosper/api

Zoosper_Api module for Zoosper CMS.

## Responsibilities

- Composer type: `zoosper-module`.
- `module.php` exposes module discovery metadata.
- Namespace `Zoosper\Api\` maps to `src/`.

## Architecture

- `src/Controller/`

## Configuration

- `config/api_routes.php`: API routes.
- `config/controllers.php`: Controller factories.
- `config/logging.php`: Module log channel/file.

## Routes

- `GET /api/v1/health` from `config/api_routes.php`.
- `GET /api/v1/hello` from `config/api_routes.php`.
- `POST /api/v1/auth/login` from `config/api_routes.php`.
- `POST /api/v1/auth/logout` from `config/api_routes.php`.
- `GET /api/v1/me` from `config/api_routes.php`.
- `GET /api/v1/content/page` from `config/api_routes.php`.

## Dependencies

- `php`: `^8.5`.
- `zoosper/auth`: `dev-dev`.
- `zoosper/core`: `dev-dev`.
- `zoosper/page`: `dev-dev`.
- `zoosper/site`: `dev-dev`.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.
- API routes should remain stateless unless their route contract explicitly states otherwise.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest app/zoosper-api/tests`.
- Current regression files discovered: `1`. Use `find app/zoosper-api/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

### Authentication security
Password-based API session login is rate-limited through the canonical authentication limiter. Accounts with active 2FA are refused with `second_factor_required`; the API never promotes those accounts from password-only authentication. API 2FA challenge/token design remains a separate explicit contract.

- `GET /api/v1/token/me` provides stateless bearer-token identity inspection.

- `GET /api/v1/pages` and `GET /api/v1/pages/{id}` require a PAT with `pages:read` and an owner with `page.view` or `page.manage`.

- `POST /api/v1/pages` and `PATCH /api/v1/pages/{id}` require `pages:write` plus current `page.manage`; creates are draft-only.
- `POST /api/v1/pages/{id}/publish` and `/unpublish` require `pages:publish` plus current `page.manage`.
- `GET /api/v1/pages/{id}/revisions` requires `pages:read` plus current `page.view` or `page.manage`; restoration requires `pages:write` plus current `page.manage`. All routes are stateless and Site-isolated.

- `GET /api/v1/menus`, `GET /api/v1/menus/{id}`, and `GET /api/v1/menus/{id}/tree` require `menus:read` plus current `menu.manage`; all are stateless and request-Site isolated.

- Menu and Menu-item create/update, guarded item deletion, disable/restore, and guarded permanent deletion require `menus:write` plus current `menu.manage`; request Site and relationship authority remain server-owned.
