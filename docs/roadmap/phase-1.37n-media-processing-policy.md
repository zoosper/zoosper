# Phase 1.37n - Media processing policy and derivative architecture

## Goal

Define the media processing contract before adding thumbnails, WebP conversion or queue-backed workers.

## Implemented

- Added immutable derivative policy classes to the media package.
- Added `MediaProcessorInterface` so future processors are swappable.
- Added `MediaProcessingResult` for future processor success/failure reporting.
- Registered `MediaProcessingPolicy` as a media service.
- Added regression tests for default profiles, validation rules, service registration and processor contracts.
- Documented original/derivative/cache path strategy.

## Important non-goals

This phase does not process images yet. It does not require GD, Imagick, Redis, RabbitMQ, S3, Azure or any background worker.

## Next phase

Phase 1.37o should prepare `zoosper/media` for true standalone repository workflow, or Phase 1.37n.1 can add a first local non-queued processor if immediate thumbnail generation is preferred.
