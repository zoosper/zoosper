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
