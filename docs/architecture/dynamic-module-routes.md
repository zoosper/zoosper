# Dynamic Module Route Discovery

Phase 0.7 moves route registration into module-owned config files.

## Why

Marketplace modules should be able to add admin/API routes without editing `ApplicationFactory`.

## Discovery flow

```text
ModuleRegistry
  -> scans enabled modules in app/* and modules/*
ModuleRouteLoader
  -> reads config/admin_routes.php and config/api_routes.php
  -> validates method/path/controller/action
  -> resolves controller instance from the application controller map
  -> falls back to zero-argument controller construction when possible
Router
  -> maps discovered routes
```

## Admin route example

```php
<?php

declare(strict_types=1);

use Vendor\Module\Controller\Admin\ExampleController;

return [
    [
        'method' => 'GET',
        'path' => '/admin/example',
        'controller' => ExampleController::class,
        'action' => 'index',
        'permission' => 'example.manage',
    ],
];
```

## API route example

```php
<?php

declare(strict_types=1);

use Vendor\Module\Controller\Api\ExampleController;

return [
    [
        'method' => 'GET',
        'path' => '/api/v1/example',
        'controller' => ExampleController::class,
        'action' => 'show',
        'public' => true,
    ],
];
```

## Current security model

Controllers still perform their own permission and CSRF checks. The route config stores `permission` metadata now so a later policy middleware layer can enforce it consistently before controller execution.

## Next improvement

Add an admin user/role/permission UI so permissions are manageable from the browser rather than seed files.
