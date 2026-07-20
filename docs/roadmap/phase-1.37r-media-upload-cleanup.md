# Phase 1.37r - Media upload cleanup and orphan-file regression coverage

## Goal

Address Mr S's highest-priority media correctness issue: storage can succeed while DB persistence fails, leaving orphan files.

## Implemented

- Added `MediaUploadService` as the shared upload orchestration service.
- Added `MediaUploadServiceResult` value object.
- Registered `MediaUploadService` in the media service configuration.
- Migrated the Editor.js upload controller to delegate validation/storage/persistence to the service while preserving existing constructor compatibility.
- Added regression coverage for cleanup contract, result contract, service registration and controller delegation.
- Documented the orphan-file failure path and cleanup policy.

## Notes

This phase aggressively starts the cleanup without rewriting the full admin media library controller blindly. The Editor.js controller was safe to migrate because its active code path and response contract have been exercised heavily in the recent browser smoke phases. The normal media admin upload controller should be migrated to this service in a follow-up once its full view/redirect/flash dependencies are reviewed.

## Next phase options

- Phase 1.37r.1: migrate normal admin media upload controller to `MediaUploadService`.
- Phase 1.37r.2: add behaviour-level upload controller tests with fake storage/repository failure paths.
- Phase 1.37n.1: add local derivative processor after upload persistence is hardened.
