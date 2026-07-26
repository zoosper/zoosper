# Composer & marketplace modules

Modules can ship as Composer packages without living under `app/` or `modules/`.

## Discovery locations

```text
app/<module>/module.php
modules/<module>/module.php
modules/<vendor>/<module>/module.php
vendor packages with composer.json type zoosper-module
vendor packages with extra.zoosper.module pointing at module.php
```

Discovery reads Composer installed-package metadata — it does not recursively scan all of `vendor/`.

## Package composer.json example

```json
{
  "name": "acme/zoosper-blog",
  "type": "zoosper-module",
  "autoload": {
    "psr-4": {
      "Acme\\ZoosperBlog\\": "src/"
    }
  },
  "extra": {
    "zoosper": {
      "module": "module.php"
    }
  }
}
```

## module.php dependencies

```php
<?php

declare(strict_types=1);

return [
    'name' => 'acme-blog',
    'enabled' => true,
    'sort_order' => 800,
    'depends' => [
        'zoosper-core',
        'zoosper-page',
    ],
];
```

## Documentation ownership

Large package-specific doc sets should live beside the package (for example `packages/zoosper-media/docs/`). Root [guide/index.md](index.md) links cross-cutting topics; package READMEs link deep implementation notes.

## Related guides

- [Modularity & modules](modularity-and-modules.md)
- [Schema & database](schema-and-database.md)
