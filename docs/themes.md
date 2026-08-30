# Theme architecture and template overrides

Themes provide frontend templates, layout structures, and assets for site rendering. Each Site in Zoosper is associated with a Theme code (defaulting to `default`), allowing multi-site installations to share or customize visual presentation while maintaining strong site isolation.

## Directory structure

Themes reside under the `themes/` directory at the project root:

```text
themes/
├── default/
│   ├── config/
│   │   └── theme.php
│   ├── templates/
│   │   ├── layout.php (or layout.latte)
│   │   ├── page.php (or page.latte)
│   │   ├── partials/
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── modules/
│   │   │   └── page/
│   │   │       └── content.php
│   │   └── overrides/
│   └── assets/
│       ├── css/
│       └── js/
└── custom-theme/
    ├── config/
    │   └── theme.php
    └── templates/
        └── ...
```

## Template resolution and inheritance

Zoosper resolves templates through a deterministic, path-safe fallback hierarchy implemented by `Zoosper\Theme\Template\TemplateRenderer`:

1. **Custom Theme Overrides**: `themes/{themeCode}/templates/overrides/{template}`
2. **Custom Theme Base**: `themes/{themeCode}/templates/{template}`
3. **Default Theme Overrides** (if current theme is not `default`): `themes/default/templates/overrides/{template}`
4. **Default Theme Base**: `themes/default/templates/{template}`

When rendering module-namespaced templates (e.g. `page::view`), the resolution candidate chain inspects:
1. `themes/{themeCode}/templates/modules/{moduleName}/{template}`
2. `themes/default/templates/modules/{moduleName}/{template}`
3. `app/{moduleName}/resources/views/{template}` (or `packages/{moduleName}/resources/views/{template}`)

This fallback system ensures that adopter themes can override specific components or entire page layouts without modifying core modules or copying unmodified templates.

## Template engines

Zoosper supports pluggable template engines registered through `TemplateEngineRegistry`:

- **Latte Engine (`.latte`)**: The default engine, providing compile-time template safety, automatic contextual HTML escaping, block macros, and clean syntax.
- **PHP Engine (`.php`)**: Standard PHP template rendering with helper functions.

The renderer automatically matches file extensions based on available registered engines.

## Shared view context and helpers

Templates have access to global and route-specific view data provided by `TemplateViewContextProvider`:

- `$siteContext`: Current resolved `SiteContext` entity.
- `$site`: Active `Site` instance (name, code, default status).
- `$siteId`: ID of the current site.
- `$siteName`: Escaped name of the site.
- `$e`: Helper callable for explicit HTML escaping (`htmlspecialchars`).
- `$partial(string $name, array $data = [])`: Helper callable for including theme partials from `partials/`.
- `$slot(string $name, array $data = [])`: Helper callable for rendering layout update slot injections.
- `$asset(string $path)`: URL generator for static assets.

## Layout updates and slot injections

Themes and modules can inject markup into named layout slots dynamically using the layout update system (`LayoutUpdateRepository`):

- **Injections**: Modules can register template injections targeting specific layout slots (e.g. `header.scripts`, `footer.scripts`, `sidebar.bottom`).
- **Replacements**: Specific template components can be remapped to alternative views per layout handle.
- **Removals**: Templates can be conditionally removed without altering theme files.

## Best practices for theme adopters

1. **Consume prepared view models**: Templates should render data passed by controllers and context providers rather than executing SQL queries or instantiating repositories directly.
2. **Always escape untrusted output**: Use Latte's automatic escaping or the `$e()` helper in PHP templates for any user-authored or database-persisted text.
3. **Keep assets path-safe**: Store theme-specific assets in `themes/{themeCode}/assets/` and generate URLs using the `$asset()` helper or relative asset paths.
