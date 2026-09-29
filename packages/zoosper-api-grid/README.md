# zoosper/api-grid

Transport and mapping adapters for external API-backed Zoosper grids.

The transport preserves only bounded integration metadata required by generic reliability and pagination handling: `link` and `retry-after`. Arbitrary response headers, cookies and credentials are not retained in `ApiResponse`. Feature response mappers remain responsible for validating endpoint-specific pagination metadata without following remote URLs directly.

Endpoint-specific mappers may use `ApiLinkRelations` to validate bounded HTTPS `Link` metadata and extract only opaque `next` and `prev` cursor tokens. The parser never follows or returns remote URLs, rejects credentials, fragments, duplicate relations, line injection and oversized metadata, and treats an absent header as a terminal page.

## Responsibilities

- Composer type: `zoosper-module`.
- Namespace `Zoosper\ApiGrid\` maps to `src/`.

## Architecture

- `src/Authentication/`
- `src/Definition/`
- `src/Mapping/`
- `src/Page/`
- `src/Transport/`
- `src/ApiGridDataSource.php`

## Dependencies

- `ext-curl`: `*`.
- `php`: `^8.5`.
- `zoosper/grid`: `^0.3.1@alpha`.
- Development dependencies: `pestphp/pest`, `pestphp/pest-plugin`, `phpunit/phpunit`.

## Security and compatibility

- Preserve public interfaces, route permissions, configuration keys, and service identifiers when extending or replacing behaviour.

## Testing

- Full repository suite: `zcomposer test`.
- Package suite: `php8.5 vendor/bin/pest packages/zoosper-api-grid/tests`.
- Current regression files discovered: `4`. Use `find packages/zoosper-api-grid/tests -type f -name '*Test.php' | sort` for the live list.
- Standard quality gate: `php8.5 tools/gate.php`.

## Operational notes

- Run commands from the repository root with PHP 8.5 or the `zcomposer` wrapper.
- Keep this README current when routes, configuration manifests, dependencies, migrations, public contracts, or operational behaviour change.
- Canonical cross-module documentation remains under `docs/`; this README is the package-level technical reference.

## Response integrity

The cURL transport stops acquisition before a response exceeds the configured byte ceiling. Every successful `ApiResponse` also carries the received-body byte count so the data-source boundary can enforce the same policy for replaceable transports before mapping. Transport failures and schema mismatches use stable, payload-free categories; exception messages must not include URLs, headers, credentials, raw response bodies, or personal and transactional values.
