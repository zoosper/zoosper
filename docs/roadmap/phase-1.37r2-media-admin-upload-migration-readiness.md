# Phase 1.37r.2 - Media admin upload migration readiness

## Goal

Prepare the normal media admin upload controller for migration to the shared `MediaUploadService` without guessing its exact redirect/view dependencies.

## Implemented

- Added `tools/audit-media-upload-controller-duplication.php`.
- Added `tools/dump-media-admin-upload-controller-1.37r2.php`.
- Added readiness tests for the audit and dump tools.
- Documented the migration target and operational commands.

## Why this is still aggressive

This phase advances the migration safely by making the remaining duplication visible and repeatably auditable. The next implementation phase can use the source dump to replace the normal admin upload flow precisely, instead of risking a blind rewrite.

## Next phase

Phase 1.37r.3 should migrate `MediaAdminController::upload()` to `MediaUploadService` using the exact source dump if the audit confirms direct storage/repository calls remain.
