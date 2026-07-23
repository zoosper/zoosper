# Composer Local Package Repository Repair

## Problem

Deployment failed during `composer update` because root `composer.json` requires `zoosper/media *@dev`, and `zoosper/media` requires `zoosper/core *@dev`, but Composer could not find `zoosper/core`.

This indicates that at least one local package dependency is not visible to Composer's dependency resolver through root repositories.

## Likely cause

The project has been moving toward package/module ownership. During that transition, local package references can drift when:

- a package declares `require: { "zoosper/core": "*@dev" }`;
- the local `zoosper/core` package exists but is not listed as a root path repository;
- or the root package name does not match the package dependency name;
- or a local path repository exists for `zoosper/media` but not for `zoosper/core`.

## Repair strategy

Use local Composer path repositories for internal packages that have their own `composer.json`.

The repair tooling should:

1. scan root, `app/`, and `packages/` for local `composer.json` files;
2. collect package `name` values;
3. add missing local package paths to root `repositories`;
4. avoid inventing packages that do not exist;
5. create a backup before writing root `composer.json`;
6. keep the operation repeatable and idempotent.

## Tools

```text
tools/audit-composer-local-packages.php
tools/apply-composer-local-package-repositories.php
```

## Safe command sequence

```bash
php8.5 tools/audit-composer-local-packages.php
php8.5 tools/apply-composer-local-package-repositories.php
php8.5 tools/apply-composer-local-package-repositories.php --apply
php8.5 $(which composer) update --no-interaction
php8.5 vendor/bin/pest
```

## Important note

If the audit cannot find a local package named `zoosper/core`, then the correct fix is not merely adding a repository. In that case, either:

- create/expose the local core Composer package with name `zoosper/core`; or
- update dependent packages to require the correct existing package name.
