# zoosper/github-issues

Read-only second API Grid pilot for GitHub repository issues. It proves a list-shaped external envelope, mixed Issue and Pull Request rows, and opaque Link-header cursor pagination. Only bounded presentation fields are mapped. Bodies, users, remote URLs, credentials, and arbitrary response headers are not retained or rendered.

## Responsibilities

- Provide an optional, permission-protected Admin destination for one deployment-owned public GitHub repository.
- Map GitHub issue and pull-request summary rows into bounded, escaped Grid presentation fields.
- Consume validated `Link` response metadata as opaque `after` and `before` cursor tokens.
- Fail closed on malformed repository identity, cursor metadata, response envelopes, row fields, labels, and timestamps.
- Remain read-only, export-free, mutation-free, and absent from the runtime surface when disabled.

## Architecture

The package owns its request mapper, response mapper, safe row mapper, data-source factory, deployment gate, Admin controller, route, menu declaration, settings defaults, and permission migration. Generic transport hardening and response-header validation remain in `zoosper/api-grid`; transport-neutral cursor values remain in `zoosper/grid`. The feature controller renders a compact escaped table and application-local cursor links without treating cursor results as numbered pages.

## Configuration

Set `GITHUB_ISSUES_ENABLED=true` with deployment-owned `GITHUB_ISSUES_OWNER` and `GITHUB_ISSUES_REPOSITORY`. Owner and repository values are validated as bounded path segments. When disabled, the package contributes no Admin route, menu item, or controller service.

## Dependencies

Runtime dependencies are `zoosper/api-grid`, `zoosper/auth`, `zoosper/core`, `zoosper/database`, and `zoosper/grid`. The integration uses the shared Admin layout and session contracts, the canonical Admin URL generator, module-owned migrations, and the API Grid reliability boundary. It does not depend on a GitHub SDK and does not require a bearer token for this public pilot.

## Extension points

A future integration may replace the repository selection policy or add authenticated GitHub transport through existing API Grid authentication contracts. Such changes must preserve redaction-safe diagnostics, bounded response sizes, local-only Admin navigation, and explicit capability declarations.

## Testing

Run the package suite from the repository root:

```bash
php8.5 vendor/bin/pest packages/zoosper-github-issues/tests
```

Run the canonical full project suite with:

```bash
zcomposer test
```

Blocking static analysis and strict quality remain required:

```bash
php8.5 vendor/bin/psalm --no-cache
php8.5 tools/gate.php --strict
```

## Operational notes

The protected page is available at `/admin/github-issues` only when enabled and authorised through `github_issue.view`. The integration performs public GET requests only, follows no response URLs, exposes no export or mutation action, and converts remote failures into a generic unavailable response. Repository identity belongs to deployment configuration, not request input. Cursor tokens are opaque and must never be logged, expanded into remote links, or presented as numbered pagination.
