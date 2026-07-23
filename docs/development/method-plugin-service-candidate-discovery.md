# Phase 1.42a-c: Method Plugin Service Candidate Discovery

## Goal

Discover possible internal service method candidates for future method-plugin report-only opt-in while keeping runtime interception disabled by default.

## What this phase does

- Scans selected `app/*/src` directories for public class methods.
- Assigns simple planning scores based on source path and method name signals.
- Writes candidate text and JSON reports under `var/reports`.
- Creates a report-only candidate plan without enabling any runtime execution.

## Safety model

This phase does not change runtime services, does not register plugins for production services, and does not enable any invocation allow-list.

## Verification

```bash
php8.5 tools/discover-method-plugin-service-candidates.php
php8.5 tools/plan-method-plugin-report-only-candidates.php
php8.5 tools/audit-method-plugin-service-candidate-discovery.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginServiceCandidateDiscoveryTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.42d-f should pick one safe candidate and build a dedicated disabled-by-default report-only proof for that exact invocation key.
