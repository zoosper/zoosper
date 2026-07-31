# API-first template engine architecture

Zoosper is API-first. Themes are an optional presentation layer over content and
application APIs, not a platform-wide dependency. Latte is Zoosper's current and
default template engine, but it is not a mandatory engine for extensions or
headless clients.

## Public engine contract

A template engine implements:

```php
use Zoosper\Theme\Template\Engine\TemplateEngineInterface;

final class CustomTemplateEngine implements TemplateEngineInterface
{
    public function extensions(): array
    {
        return ['custom'];
    }

    public function renderFile(string $path, array $data): string
    {
        // Render the file with the engine of your choice.
    }
}
```

Register the implementation through module service configuration and add it to
`TemplateEngineRegistry`. The registry selects an implementation by normalised
file extension. A later, higher-priority module may replace one extension while
leaving other engines available.

## Boundary rules

- API and Core source must not import Latte or `LatteTemplateEngine`.
- `zoosper-theme` owns the default Latte adapter and service binding.
- Modules and themes depend on `TemplateEngineInterface`, not on Latte classes.
- Explicit template extensions select their registered engine.
- Extensionless resolution may probe the registry's available extensions.
- Headless API consumers do not require a server-side template engine.

## Current default

Zoosper uses Latte as its current default because that is the engine selected by
the Theme module's service configuration. Switching or extending rendering
should be an implementation/configuration concern, not a Core or API refactor.
