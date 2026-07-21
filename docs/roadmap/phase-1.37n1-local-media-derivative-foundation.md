# Phase 1.37n.1 - Local media derivative foundation

## Goal

Add the safe local filesystem foundation needed before implementing actual GD/Imagick media derivative processors.

## Implemented

- Added `LocalMediaDerivativePath`.
- Added `LocalMediaDerivativePathResolver`.
- Added `LocalMediaDerivativeWriter`.
- Added package-local derivative foundation audit tooling.
- Added tests for deterministic path generation, traversal rejection and derivative byte writing.
- Added package-owned architecture and operations docs.
- Added root operations link doc.

## Non-goal

This phase does not resize images. It prepares the safe local path and write conventions that future `MediaProcessorInterface` implementations can use.

## Next phase

Implement a concrete no-op/copy processor adapter or GD-backed derivative processor depending on whether we want engine-free behaviour first or an optional `zoosper/media-gd` package split.
