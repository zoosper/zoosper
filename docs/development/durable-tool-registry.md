# Durable Tool Registry (Single Source of Truth)

## Purpose

Today's cleanup incident happened because two lists — the gate's hygiene allowlist
and the test-referenced durable registry — could drift apart. A tool could be
"durable per tests" but "disposable per gate". This document describes the unified
approach that prevents recurrence.

## Canonical manifest

`config/durable-tools.php` is the single source of truth:

```php
return [
    'tools/apply-role-admin-latte-cutover.php' => ['reason' => '...'],
    // ...
];
```

- Keys are repo-relative tool paths.
- Each entry carries a human-readable `reason`.

## Consumers

- `tools/gate.php` loads the manifest for the `tools:hygiene` exemptions and for the
  `durable-registry:integrity` check.
- The `DurableToolRegistry` class should return the same manifest so tests and gate
  agree by construction:

  ```php
  return require dirname(__DIR__, N) . '/config/durable-tools.php';
  ```

## Integrity guarantees

`tools/gate.php` runs `durable-registry:integrity`, which fails when:

- the canonical manifest file is missing;
- a manifest-listed tool is absent on disk;
- a manifest entry has an empty reason.

This makes drift a hard, immediate failure instead of a latent bug.

## Adding or retiring a durable tool

1. To add: create the tool, then add its path + reason to `config/durable-tools.php`.
2. To retire: remove its tests/registry usage first, then delete the manifest entry,
   then delete the file. The integrity check will confirm consistency.
