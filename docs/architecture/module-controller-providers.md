# Module Controller Providers

Phase 0.16 starts moving controller mapping away from `ApplicationFactory` and into modules.

## Problem

`ApplicationFactory` was growing every time a module added a controller.

## Solution

Modules can now declare controller factories in:

```text
app/<module>/config/controllers.php
modules/<vendor-module>/config/controllers.php
```

The new `ControllerProviderLoader` reads those files and builds the controller map using a shared `ServiceContainer`.

## Benefit

Future marketplace-style modules can become more plug-and-play:

- add `module.php`
- add `config/admin_routes.php`
- add `config/admin_menu.php`
- add `config/controllers.php`
- add templates/resources

No core file should need to change for normal controller registration.
