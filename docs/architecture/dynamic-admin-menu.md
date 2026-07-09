# Dynamic Admin Menu Architecture

This phase moves admin navigation from hardcoded PHP arrays into module-provided configuration files.

## Why

Zoosper should eventually support marketplace-style modules. A module should be able to contribute admin navigation without editing core files.

## Discovery flow

```text
ModuleRegistry
  -> scans app/*/module.php and modules/*/module.php
  -> enabled modules only
AdminMenuLoader
  -> reads each enabled module's config/admin_menu.php
  -> normalises menu entries
AdminMenu
  -> filters entries by current admin permissions
AdminLayout
  -> renders grouped sidebar navigation
```

## Module menu file example

```php
<?php

declare(strict_types=1);

return [
    [
        'code' => 'example',
        'label' => 'Example',
        'url' => '/admin/example',
        'permission' => 'example.manage',
        'sort_order' => 40,
        'group' => 'Content',
    ],
];
```

## Current limitations

This phase discovers menu entries only. A marketplace module still needs its admin route to be wired. The next extension phase should add dynamic admin route discovery using module `config/admin_routes.php` files.
