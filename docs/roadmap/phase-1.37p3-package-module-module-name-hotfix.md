# Phase 1.37p.3 - Package module module-name hotfix

## Goal

Fix generated Zoosper module names and namespaces for camel-cased module names.

## Diagnosis

`Acme/MovieLibrary` correctly generated the Composer package name after Phase 1.37p.2, but the Zoosper module name still became:

```text
Acme_Movielibrary
```

because `studly()` lowercased every part before capitalising it.

## Implemented

- Changed `studly()` to preserve existing camel-case boundaries while still capitalising lowercase words.
- Added regression coverage for module name, namespace and generated test filename.

## Expected result

`Acme/MovieLibrary` now produces:

```text
Acme_MovieLibrary
Acme\MovieLibrary\
packages/acme-movie-library/tests/Unit/MovieLibraryPackageTest.php
```
