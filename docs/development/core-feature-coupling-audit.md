# Phase 1.67a-l: Core Feature Coupling Audit

## Purpose

This phase starts the next reviewer-driven architecture arc after the Page Momentum cleanup: core/feature decoupling.

The audit scans `app/zoosper-core/src` for direct references to feature/module namespaces:

- `Zoosper\Page\`
- `Zoosper\Site\`
- `Zoosper\Auth\`
- `Zoosper\Theme\`
- `Zoosper\Media\`
- `Zoosper\Admin\`
- `Zoosper\Api\`

## Commands

Report mode:

```bash
php8.5 tools/audit-core-feature-coupling.php
```

Strict mode:

```bash
php8.5 tools/audit-core-feature-coupling.php --strict
```

## Output

```text
var/reports/core-feature-coupling.txt
var/reports/core-feature-coupling.json
```

## Safety

The audit is read-only. It does not modify runtime code.
