# Zoosper Admin Form

Unified Admin Form kernel for Zoosper CMS.

## Responsibilities

- Providing an object-oriented foundation for admin forms.
- Decoupling form presentation logic from Core and specific modules.
- Handling generic form rendering (fields, sections, validation errors).
- Supporting complex UI requirements like file uploads and HTML blocks.

## Dependencies

- `php: >=8.5`
- `marko/core`

## Features

- Declarative form definitions.
- Modern registry-based form discovery.
- Support for complex field types (checkbox-list, HTML blocks, file uploads).
- Section-based layout.
- Automatic CSRF protection.

## Usage

```php
use Zoosper\AdminForm\AdminFormDefinition;
use Zoosper\AdminForm\AdminFormField;
use Zoosper\AdminForm\AdminFormRegistry;

$registry = new AdminFormRegistry();
$registry->register(new AdminFormDefinition(
    'my.form',
    [
        new AdminFormField('name', 'text', 'Name', 10),
    ]
));
```

## Operational notes

- Use `bin/zoosper compile` to refresh module manifest if new forms are added via `admin_ui.php`.
- The form renderer automatically generates CSRF tokens if a token is provided to the `render()` method.

## Testing

Run tests via Pest or the project gate:

```bash
zcomposer test
# or
php8.5 tools/gate.php
```

Or manually:

```bash
vendor/bin/pest
```
