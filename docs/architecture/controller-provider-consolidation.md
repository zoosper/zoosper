# Controller Provider Consolidation

This patch moves controller construction out of `ApplicationFactory` and into module-owned `config/controllers.php` files.

## Why

`ApplicationFactory` was growing each time a feature added a controller. That makes modules less plug-and-play.

## New pattern

Each module can now provide:

```text
config/controllers.php
```

Each file returns:

```php
return [
    ControllerClass::class => static fn (ServiceContainer $services): ControllerClass => new ControllerClass(...),
];
```

`ApplicationFactory` registers shared services once, then calls:

```php
$controllers = (new ControllerProviderLoader($modules, $services))->load();
```

Routes continue to come from each module's `config/admin_routes.php` and `config/api_routes.php`.
