# Project layout

Zoosper separates responsibilities at the repository root so the webroot stays minimal and modules stay replaceable.

```text
app/         First-party Zoosper modules (zoosper-core, zoosper-page, …)
modules/     Project-specific or third-party modules (preferred for custom work)
packages/    Standalone package modules (e.g. zoosper-media)
themes/      Theme templates and assets
public/      Webroot only (index.php, static assets)
storage/     Private files (database, uploads originals)
var/         Runtime logs, cache, generated reports
config/      Application config (overrides module defaults)
database/    File-based migrations used alongside schema engine
docs/        Documentation (canonical guides in docs/guide/)
bin/         CLI entrypoints (zoosper, verify, schema tools)
```

## Webroot rule

`public/` must **not** contain source or runtime trees such as `app`, `config`, `modules`, `vendor`, `var`, or `storage`. Only controlled entrypoints and published assets belong there.

Allowed surface includes:

```text
public/index.php
public/assets/
public/static/
public/media/   (published media copies after validation)
```

## Module locations

| Location | Use |
|----------|-----|
| `app/zoosper-*` | Core product modules shipped with Zoosper |
| `modules/<name>/` or `modules/<vendor>/<name>/` | Custom or community modules |
| `packages/<name>/` | Extractable Composer-style packages with `module.php` |
| `vendor/` | Composer packages with `type: zoosper-module` |

Do not edit `app/zoosper-*` for client projects; add a module under `modules/` instead.

## Request bootstrap

```text
public/index.php
  -> ApplicationFactory::create()
  -> ModuleRegistry + config + DI + routes
  -> Application::handle()
```

See [Modularity & modules](modularity-and-modules.md) for what each module can declare in `config/`.
