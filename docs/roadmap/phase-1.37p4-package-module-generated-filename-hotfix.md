# Phase 1.37p.4 - Package module generated filename hotfix

## Goal

Fix the generated package unit test filename from a literal placeholder to the module class prefix.

## Diagnosis

The file map used a single-quoted key:

```php
'tests/Unit/{$classPrefix}PackageTest.php'
```

Single-quoted PHP strings do not interpolate variables, so the scaffolder created the wrong filename and the test could not find:

```text
tests/Unit/MovieLibraryPackageTest.php
```

## Implemented

- Changed the file map key to an interpolated string.
- Added regression coverage that confirms the correct filename exists and the literal placeholder filename does not.

## Expected result

All package scaffolder tests should pass.
