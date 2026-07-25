# Phase 1.75n-z: Durable Tool Registry

## Purpose

This phase introduces a small registry for durable tools that must not be removed during cleanup phases simply because their names look temporary.

## Why

The RoleAdmin cutover executors are durable read-only tools required by existing tests. They were accidentally removed because they looked like temporary `apply-*cutover.php` helpers.

The registry makes that distinction explicit.

## Files

```text
config/durable_tools.php
```

## Audit

```bash
php8.5 tools/audit-durable-tool-registry.php
```

## Rule

If a cleanup phase wants to remove a registered durable tool, it must first update the owning tests/docs and then update the registry in the same phase.
