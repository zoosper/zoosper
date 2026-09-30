# Developer guide

## Repository layout

- `app/`: first-party application modules
- `packages/`: extracted Composer packages
- `config/`: project configuration and overrides
- `database/`: root migration entry points
- `public/`: web entry point and project-owned public assets
- `themes/`: frontend themes
- `tests/`: shared tests where present
- `tools/`: durable repository tooling listed in `config/durable-tools.php`

## Development workflow

Run focused Pest tests first, then the full suite. Before committing, run module compilation, the strict gate and release checks.

## Design rules

Controllers are thin HTTP adapters. Business rules belong in services. Persistence belongs in repositories. Templates own markup. Modules expose contracts through configuration, services, routes, permissions, assets and migrations.

## API Grid generator

Run `php8.5 bin/zoosper make:api-grid Acme/RemoteRecords --key=acme.remote-records --route=/admin/remote-records` to create a standalone package skeleton. The generated integration is deliberately disabled. Developers must implement endpoint-specific mapping, deployment-owned base URL and credentials, permissions, controller and feature presentation before enabling its route or menu.

The scaffold provides bounded first-party dependencies, request and response mapper starting points, valid and malformed response fixtures, a package test, export-ignore policy and complete package documentation headings. It never edits root Composer metadata automatically. Review the package, add its path repository and requirement explicitly, refresh the lock file, then run focused tests, Psalm, strict quality and the complete release gates.

### Testing fixtures and maintained example

Use `Zoosper\ApiGrid\Testing\FakeApiTransport` to queue deterministic responses and inspect mapped requests without networking. Use `ApiResponseFixture::success()` and `ApiResponseFixture::failure()` to construct bounded transport results. The source-only integration under `examples/api-grid-integration/` demonstrates deployment-owned scope, request mapping, strict response mapping and a neutral numbered Grid result.

### API Grid compatibility and upgrade policy

API Grid public interfaces, failure categories, configuration keys, service identifiers, capability meanings and cursor semantics are compatibility boundaries. Compatible additions may add optional metadata, capabilities or helpers without changing existing defaults. A breaking signature, category, required configuration, pagination meaning or security default requires a new minor release line and an explicit migration note. First-party packages remain on the synchronised release train with bounded `^0.3.1@alpha` dependencies. Deploy the committed root lock file, review generated packages before registration, and rerun focused tests, Psalm, strict quality, the full suite, module compilation and release checks after any upgrade.
