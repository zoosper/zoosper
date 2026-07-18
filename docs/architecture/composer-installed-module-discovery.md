# Composer-installed module discovery

Phase 1.37g extends `ModuleRegistry` so Zoosper can discover modules from Composer-installed packages as well as the historical `app/*` layout.

## Discovery locations

```text
app/*/module.php
packages/*/module.php
modules/*/module.php
modules/*/*/module.php
vendor/*/*/composer.json with extra.zoosper.module
```

A Composer package exposes its module file with:

```json
{
  "extra": {
    "zoosper": {
      "module": "module.php"
    }
  }
}
```

## Why this matters

This is the next step after the media path repository pilot. Once Composer-installed modules are discovered directly from `vendor/`, the temporary `app/zoosper-media` compatibility symlink can be removed in a later cleanup phase.
