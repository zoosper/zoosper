# Zoosper Media

`zoosper/media` is the first Zoosper first-party module prepared for Magento-style package ownership.

## What this module owns

```text
- Media admin routes and controllers
- Media upload validation and storage services
- Media asset metadata repository
- Editor.js image upload response/config contracts
- Editor.js image block sanitisation
- Media processing policy and derivative processing contracts
- Declarative media schema
- Co-located Pest unit tests
```

## Module registration

The package advertises the module through Composer metadata:

```json
{
  "type": "zoosper-module",
  "extra": {
    "zoosper": {
      "module": "module.php",
      "name": "Zoosper_Media"
    }
  }
}
```

Zoosper can therefore discover it from a local `packages/` path repository today and a future `vendor/zoosper/media` install later.

## Development inside the root project

From the Zoosper root project:

```bash
PHP=php8.5 composer dump-autoload
vendor/bin/pest packages/zoosper-media/tests/Unit
PHP=php8.5 bin/verify
```

## Standalone repository readiness

When this package is moved to its own repository, it should keep:

```text
composer.json
module.php
config/
src/
tests/
phpunit.xml.dist
README.md
.github/workflows/tests.yml
```

The package still depends on `zoosper/core`, `zoosper/admin` and `zoosper/auth`. Until those packages are separately published, standalone installs should use local path repositories or the root monorepo workflow.

## Browser/editor smoke

The root project owns browser smoke because it includes the admin shell, auth middleware, database connection and frontend rendering. Use the root project checklist in:

```text
docs/operations/editorjs-media-browser-smoke.md
```
