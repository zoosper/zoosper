# Themes & templates

Zoosper renders through a **template engine registry** with Latte as the recommended engine and PHP templates as fallback.

## Architecture

```text
TemplateRenderer
  -> TemplateEngineRegistry
      -> LatteTemplateEngine (.latte)
      -> PhpTemplateEngine (.php)
```

Extensionless names resolve to `.latte` or `.php` depending on registered engines and available files.

## Module view namespace

Use Marko-style `module::path` syntax:

```text
zoosper-page::page/view
```

Resolution order for frontend theme `default`:

```text
themes/default/templates/modules/zoosper-page/page/view.latte
themes/default/templates/modules/zoosper-page/page/view.php
app/zoosper-page/resources/views/page/view.latte
app/zoosper-page/resources/views/page/view.php
```

Non-default themes check the active theme first, then fall back to `default`.

Admin templates follow the same module ownership pattern under `resources/views/admin/...`.

## Theme selection

Sites carry a `theme_code` (see [Sites, pages & content](sites-pages-and-content.md)). Layout and asset wiring respect the selected theme and module asset registries.

## Static assets

Published assets live under configured paths such as:

```text
/assets/admin
/assets/frontend
/assets/modules
```

Keep asset URLs separate from `/admin` application routes.

## Overriding without forking

1. Copy module template path into `themes/<theme>/templates/modules/<module>/...`.
2. Adjust markup/CSS; controller and routes stay unchanged.

This matches Zoosper’s “extend without editing core” rule.

## Related guides

- [Admin interface](admin-interface.md)
- [Security foundations](security-foundations.md) — escaping and sanitised HTML slots
