# Phase 1.63m-z: Page Momentum Support Artefact Cleanup

## Purpose

After quarantining code/test process artefacts, this phase targets leftover support files in `docs/` and `tools/` from the exploratory Page Momentum arc.

The goal is to keep the current live dashboard/facts/status tooling, while moving old phase/prove/audit/generate artefacts into quarantine.

## Tool

```bash
php8.5 tools/cleanup-page-momentum-support-artifacts.php
```

Dry-run is default.

Apply mode:

```bash
php8.5 tools/cleanup-page-momentum-support-artifacts.php --apply
```

## Safety

- Runtime source under `app/` is not scanned or moved.
- Keeps current dashboard facts/status/visual-shell docs and tools.
- Moves files to quarantine instead of deleting.
- Generates a restore script.

## Quarantine location

```text
var/quarantine/page-momentum-support-artifacts/<timestamp>/
```
