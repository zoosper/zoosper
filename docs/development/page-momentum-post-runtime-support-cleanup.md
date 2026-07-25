# Phase 1.66a-l: Page Momentum Post-Runtime Support Cleanup

## Purpose

After Phase 1.65m-z quarantined non-core Page Momentum runtime candidates, leftover support docs/tools may still reference removed scaffolding classes and config files.

This phase adds a dry-run-first cleanup tool for those support artefacts.

## Command

Dry-run:

```bash
php8.5 tools/cleanup-page-momentum-post-runtime-support-artifacts.php
```

Apply after review:

```bash
php8.5 tools/cleanup-page-momentum-post-runtime-support-artifacts.php --apply
```

## Safety

- Scans only `tools/`, `docs/development/`, and `docs/roadmap/`.
- Does not move runtime source under `app/`.
- Keeps current useful dashboard/facts/status/hygiene docs and tools.
- Apply mode quarantines and generates a restore script.
