# Phase 1.37h.4 — Package testsuite normalisation

## Goal

Remove duplicate Pest/PHPUnit suite warnings after package test discovery was enabled.

## Problem

The broad package test entry:

```xml
<directory>packages/*/tests</directory>
```

caused package Unit tests to be considered for multiple suites.

## Fix

Use the narrower Unit-suite path:

```xml
<directory>packages/*/tests/Unit</directory>
```

## Outcome

Extracted module Unit tests remain in the root verification suite without duplicate-suite warnings.
