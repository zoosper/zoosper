# Module Composer Manifests

Phase 1.37e adds package-ready `composer.json` manifests to first-party Zoosper modules that have PHP source code.

The manifests do not physically extract modules yet. They make each module self-describing before the first path-repository pilot.

## Manifest shape

Each package uses:

```json
{
  "type": "zoosper-module",
  "autoload": {
    "psr-4": {
      "Zoosper\\Media\\": "src/"
    }
  },
  "extra": {
    "zoosper": {
      "module": "module.php"
    }
  }
}
```

## Dependency policy

Dependencies are conservative starting points for package extraction. They should be tightened as each module is physically extracted and tested independently.

`zoosper-media` remains the first package extraction candidate.
