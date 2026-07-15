# Module Generator

Create a new module scaffold:

```bash
php bin/zoosper make:module Acme_Blog
```

Accepted name format is `Vendor_Module`. The command creates a module folder in
`app/vendor-module/` with common Zoosper extension points: services, controllers,
routes, ACL, schema, events, logging, translations, views and tests.

The generated README includes a PSR-4 autoload snippet. Add it when the module has
PHP classes under `src/`, then run `composer dump-autoload`.
