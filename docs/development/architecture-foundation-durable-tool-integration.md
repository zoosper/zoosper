# Phase 1.76a-l: Architecture Foundation Durable Tool Integration

## Purpose

This phase wires the architecture foundation gate aggregator to the durable tool registry.

## Why

The durable tool registry is the source of truth for tools that should survive cleanup phases. The foundation gate should not maintain a separate hardcoded allowlist.

## Behaviour

`tools/audit-architecture-foundation-gates.php` now loads durable tool paths from:

```text
config/durable_tools.php
```

Those tools are excluded from temporary artefact warnings even if their names match broad cleanup patterns such as `tools/apply-*cutover.php`.

## Command

```bash
php8.5 tools/audit-architecture-foundation-gates.php
```
