# Phase 1.37t - Package-owned documentation foundation

## Goal

Reduce AI context/token pressure by moving package-specific documentation toward package-owned docs folders while keeping root docs as index, website navigation and cross-package architecture.

## Implemented

- Added root package-owned documentation policy.
- Added media package docs root and media package documentation policy.
- Added operations docs for package docs migration.
- Added audit and planning tools for identifying docs that can move to package docs.
- Added tests for root and media package documentation policy.

## Non-goal

This phase does not bulk-move hundreds of docs. It establishes the policy, first package destination and audit tooling so moves can happen safely in smaller package-specific batches.

## Next phase

Phase 1.37t.1 should move the first batch of media-specific architecture and operations docs into `packages/zoosper-media/docs/`, leaving root links/indexes behind.
