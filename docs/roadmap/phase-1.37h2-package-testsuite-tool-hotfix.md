# Phase 1.37h.2 — Package testsuite tool hotfix

## Problem

The first package-testsuite synchronisation tool failed to parse in the target environment, so `packages/*/tests` was not added to root `phpunit.xml` and the new regression test correctly failed.

## Fix

Replace `tools/ensure-package-testsuites.php` with a minimal parse-safe implementation that adds:

```xml
<directory>packages/*/tests</directory>
```

to the first root PHPUnit testsuite.
