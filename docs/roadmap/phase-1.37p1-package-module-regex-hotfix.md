# Phase 1.37p.1 - Package module scaffolder regex hotfix

## Goal

Fix package module names such as `Acme/MovieLibrary` failing validation in `PackageModuleScaffolder`.

## Diagnosis

The validation regex used `/.../` as its delimiter while also allowing `/` as an input separator. The slash in the character class was not portable enough in this generated source and PHP reported:

```text
preg_match(): Unknown modifier '_'
```

## Implemented

- Switched validation and split patterns to `~...~` delimiters.
- Added a regression test covering slash, underscore and dash module separators.

## Expected result

```bash
vendor/bin/pest app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderTest.php app/zoosper-core/tests/Unit/Scaffold/PackageModuleScaffolderRegexTest.php
```

should pass.
