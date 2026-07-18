# Media Path Repository Pilot

Phase 1.37f pilots the first physical package extraction using `zoosper-media`.

The pilot is intentionally conservative:

```text
app/zoosper-media
  -> packages/zoosper-media
app/zoosper-media
  -> symlink to ../packages/zoosper-media
root composer.json
  -> path repository packages/zoosper-media
  -> require zoosper/media *@dev
```

## Why keep the app symlink?

Current runtime module discovery still expects enabled modules under `app/*/module.php`. The compatibility symlink lets us test Composer path repository semantics without changing runtime module discovery in the same phase.

A later phase can teach ModuleRegistry to discover Composer-installed modules from `vendor/` and then remove this compatibility symlink.

## First module choice

`zoosper-media` is the safest first extraction candidate because it is new, self-contained, and already has a module-level composer manifest, schema, services, routes, views and tests.
