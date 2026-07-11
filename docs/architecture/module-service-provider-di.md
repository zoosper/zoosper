# Phase 0.55 - Module service provider / DI foundation

## Problem

`ApplicationFactory.php` was manually creating and registering many concrete services. That made Zoosper module-aware but not fully module-composed.

## Solution

Modules now declare services in:

```text
app/<module>/config/services.php
modules/<module>/config/services.php
modules/<vendor>/<module>/config/services.php
```

Each file returns service IDs mapped to factories:

```php
return [
    SomeService::class => static fn (ServiceContainer $services): SomeService => new SomeService(...),
];
```

`ServiceProviderLoader` discovers enabled modules and registers those services lazily.

## Custom module location

Core Zoosper modules live in `app/zoosper-*`.

Other developers should place custom/local/community modules in:

```text
modules/acme/blog/module.php
modules/acme/blog/config/services.php
```

or for a simple local module:

```text
modules/my-custom-module/module.php
modules/my-custom-module/config/services.php
```

Custom modules can override a service by declaring the same service ID later in module order. Use `sort_order` in `module.php` to load after core modules.

## ApplicationFactory responsibility

`ApplicationFactory` should only bootstrap primitives:

```text
ConfigRepository
PDO
ModuleRegistry
LogManager/ErrorHandler
ServiceContainer
Module service providers
Routes/controllers
Application
```

Feature services belong to modules.
