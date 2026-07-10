# CLI tool bootstrap standard

Every Zoosper CLI tool should start with:

```php
$basePath = require __DIR__ . '/bootstrap.php';
```

This ensures:

- Composer autoload is loaded
- `.env` is loaded
- `env()` exists
- CLI and web config stay aligned

Avoid duplicating env helper definitions in every tool.
