# Phase 1.40d-f: Admin Form Config Runtime Migration

This phase moves admin form/UI config loading from readiness/discovery to a guarded runtime migration.

The patcher supports dry-run mode, writes `.phase140df.bak` backups before modifying source files, and reports migration state under `var/reports/`.

Use `php8.5 vendor/bin/pest` for tests when the default shell PHP is older than the Composer platform requirement.
