# Composer Module Package Roadmap

Zoosper's first-party modules are already module-shaped:

```text
app/zoosper-api
app/zoosper-auth
app/zoosper-page
app/zoosper-media
```

Phase 1.37d.1 keeps the staged package-extraction roadmap but fixes package identity detection so both legacy kebab module names (`zoosper-page`) and package-friendly names (`Zoosper_Media`) are recognised.

## Naming bridge

Historical module names can remain valid during transition:

```text
zoosper-page       -> package zoosper/page       -> namespace Zoosper\Page\
zoosper-two-factor -> package zoosper/two-factor -> namespace Zoosper\TwoFactor\
Zoosper_Media      -> package zoosper/media      -> namespace Zoosper\Media\
```

This means package-readiness work does not require renaming every existing module.php immediately.

## Extraction sequence

1. Keep modules in `app/` while package identity is audited.
2. Generate per-module `composer.json` files.
3. Move `zoosper-media` to `packages/zoosper-media` as the first path repository.
4. Replace the root PSR-4 mapping with a Composer `require` entry.
5. Run full verification.
6. Repeat one module at a time.
