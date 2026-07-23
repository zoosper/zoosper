# Composer Internal Package Stability Repair

## Problem

After local Composer path repositories were added, Composer can now find the local package `zoosper/core`. The deployment error changed to:

```text
found zoosper/core[dev-master, dev-dev] but it does not match your minimum-stability
```

That is progress: package discovery is working, but root Composer stability rules still reject internal development packages.

## Why this happens

The project uses local internal packages such as:

```text
zoosper/media
zoosper/core
```

Those packages currently resolve as development branches such as `dev-master` or `dev-dev`. If root `composer.json` does not allow dev stability, Composer may reject transitive internal package requirements even when the package is available through a local path repository.

## Decision

For this modular CMS repository, root Composer should explicitly support internal dev packages while still preferring stable third-party dependencies.

Set root Composer fields:

```json
"minimum-stability": "dev",
"prefer-stable": true
```

Additionally, explicitly require any local internal `zoosper/*` package that is required transitively but missing from root require. This makes stability intent visible at the root.

## Safety

- `prefer-stable: true` keeps third-party packages biased toward stable releases.
- The patch tool only adds explicit root requirements for local `zoosper/*` packages it can discover.
- The tool creates a backup before writing.
- The tool is idempotent.

## Command sequence

```bash
php8.5 tools/apply-composer-internal-package-stability.php
php8.5 tools/apply-composer-internal-package-stability.php --apply
php8.5 $(which composer) update --no-interaction
php8.5 vendor/bin/pest
```

## Expected result

Composer should stop rejecting `zoosper/core` solely because it resolves as `dev-master` or `dev-dev`.
