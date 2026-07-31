# ADR: Composer-Authoritative Module Version

## Status

Accepted and implemented.

## Context

Several first-party `module.php` files declared ad hoc versions while Composer
already owns installed package versions through its generated
`Composer\InstalledVersions` metadata. Duplicate version declarations drifted
from path-repository branch versions and future package tags.

## Decision

For valid Marko-native Zoosper packages, runtime version resolution is:

1. Composer `InstalledVersions` pretty version;
2. Composer normalised installed version;
3. explicit package-manifest `version` for isolated fixtures or non-installed
   package inspection;
4. the transitional module fallback only for non-Composer modules.

Remove `version` from all first-party `module.php` files.

## Consequences

- Runtime diagnostics report the version Composer actually installed.
- Path repositories consistently report their development branch version.
- Future tags automatically become runtime module versions.
- `module.php` no longer duplicates package identity or release metadata.
- A fallback remains only for transitional non-Composer modules.
