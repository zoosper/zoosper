# Phase 0.56 - Composer marketplace module discovery

## Goal

Allow Zoosper modules installed via Composer into `vendor/` to be discovered automatically without scanning every vendor package recursively.

## Discovery locations

Zoosper discovers modules from:

```text
app/<module>/module.php
modules/<module>/module.php
modules/<vendor>/<module>/module.php
vendor packages where composer.json type is zoosper-module
vendor packages where composer.json extra.zoosper.module is set
```

## Marketplace package composer.json

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

## module.php

```php
return [
    'name' => 'acme-blog',
    'enabled' => true,
    'sort_order' => 800,
    'depends' => [
        'zoosper-core',
        'zoosper-page'
    ],
];
```

## Why metadata-based discovery

Zoosper reads Composer installed package metadata instead of recursively scanning vendor folders. This keeps discovery explicit, safer and faster for marketplace modules.
