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
