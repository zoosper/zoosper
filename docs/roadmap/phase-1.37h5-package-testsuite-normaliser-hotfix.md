# Phase 1.37h.5 — Package testsuite normaliser hotfix

## Problem

The previous normalisation tool failed to parse before it could update `phpunit.xml`, leaving the broad `packages/*/tests` entry in place and causing the regression test to fail.

## Fix

Replace the tool with a minimal comment-free implementation that only performs the required string replacement:

```xml
<directory>packages/*/tests</directory>
```

to:

```xml
<directory>packages/*/tests/Unit</directory>
```
