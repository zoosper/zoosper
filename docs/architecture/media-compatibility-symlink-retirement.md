# Media compatibility symlink retirement

Phase 1.37f kept `app/zoosper-media` as a compatibility symlink after moving the media module to `packages/zoosper-media`.

Phase 1.37h removes that compatibility path after `ModuleRegistry` has learned to discover modules from package locations.

## Target state

```text
packages/zoosper-media/module.php
packages/zoosper-media/composer.json
```

The runtime module list should still include `Zoosper_Media`, but its source should be `packages` or `vendor`, not `app`.

## Safety rule

The removal tool only deletes `app/zoosper-media` when it is a symlink. It refuses to delete a real directory, because doing so could destroy source code if Phase 1.37f was not applied correctly.
