# Phase 1.69a-l: ApplicationFactory Fallback Cutover Plan

## Purpose

This phase prepares the runtime cutover that will remove the direct Page module import from `ApplicationFactory`.

The current audit reports the Page module coupling as:

```text
use Zoosper\Page\Controller\PageController;
```

The target direction is:

```text
ApplicationFactory -> FallbackHandlerInterface -> Page module binding
```

## Safety

This phase is read-only. It does not patch `ApplicationFactory` yet.

## Commands

```bash
php8.5 tools/plan-application-factory-fallback-cutover.php
php8.5 tools/audit-application-factory-fallback-cutover-plan.php
```

## Outputs

```text
var/reports/application-factory-fallback-cutover-plan.txt
var/reports/application-factory-fallback-cutover-plan.json
var/reports/application-factory-fallback-cutover-draft.patch.md
```

## Next phase

Phase 1.69m-z should apply a guarded runtime patch to `ApplicationFactory` using the live file shape captured by this planner.
