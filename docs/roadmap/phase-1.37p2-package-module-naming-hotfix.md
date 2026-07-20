# Phase 1.37p.2 - Package module naming hotfix

## Goal

Fix generated Composer package names for camel-cased module names.

## Diagnosis

`Acme/MovieLibrary` was producing:

```text
acme/movielibrary
```

because `studly()` lowercased `MovieLibrary` into `Movielibrary` before package-name kebab conversion could detect the camel-case boundary.

## Implemented

- Build the Composer package module segment from the original module parts before StudlyCase merging.
- Preserve class/module names as `MovieLibrary`.
- Preserve package names as `movie-library`.
- Added a regression test for camel-case boundary preservation.
