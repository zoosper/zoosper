# Troubleshooting helpful errors

## Missing service

Run:

```bash
php tools/verify-service-providers.php
```

Then check the module's `config/services.php` file.

## Invalid controller provider

Check:

```text
app/<module>/config/controllers.php
modules/<vendor>/<module>/config/controllers.php
```

A valid controller config returns an array keyed by controller class name with callable factories.

## Invalid route config

Check:

```text
config/admin_routes.php
config/api_routes.php
```

Routes must include method, path, controller and action.

## Missing module dependency

Run:

```bash
php tools/verify-module-dependencies.php
```

Then install/enable the missing dependency or update module.php.
