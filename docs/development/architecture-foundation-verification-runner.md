# Phase 1.75a-l: Architecture Foundation Verification Runner

## Purpose

This phase adds a single runner for the permanent architecture guard tools.

It is intended for fast foundation verification after architecture-heavy work.

## Runner

```bash
php8.5 tools/verify-architecture-foundation.php
```

## Guards included

- `tools/audit-core-feature-coupling.php`
- `tools/audit-core-feature-decoupling-closure.php`
- `tools/audit-site-lookup-boundary-regression.php`
- `tools/audit-site-lookup-service-binding.php`
- `tools/audit-site-lookup-service-binding-regression.php`
- `tools/audit-architecture-foundation-gates.php`

## Output

```text
var/reports/architecture-foundation-verification.txt
var/reports/architecture-foundation-verification.json
```

## Scope

The runner is read-only and audit-focused. It does not run Composer or Pest. Those remain separate release gates.
