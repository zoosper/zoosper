# API Grid second-pilot readiness

## Purpose

The Store Orders pilot proves the numbered-pagination path. The next implementation must prove that generic contracts are not accidentally shaped around Store Orders before a generator or public extension promise is added.

## Phase 7A output

This phase is deliberately a boundary and planning build. It adds a repeatable lexical audit for Store Orders-specific tokens inside `zoosper-grid`, `zoosper-admin-grid` and `zoosper-api-grid`, records the second-pilot selection criteria, and places the Settings platform immediately after API Grid closure.

## Second-pilot selection criteria

Choose a real integration only when its owner, endpoint, credentials and safe test fixture are available. Prefer a source that differs materially from Store Orders in at least one dimension:

- cursor rather than numbered pagination;
- nested or otherwise different response envelope;
- different capability set for filtering, search, sorting or export;
- different authentication strategy;
- schema evolution or rate-limit metadata that exercises diagnostics.

Do not invent a production endpoint or commit credentials. Until a real source is selected, use fake transport fixtures only for generic hardening.

## Hardening gate before the second pilot

- invalid JSON remains distinguishable from an empty result;
- timeouts and non-success responses retain safe, actionable categories;
- response size and page size remain bounded;
- mapping errors identify the contract without dumping full payloads;
- secrets and personal or transactional fields are redacted from logs;
- cursor tokens are opaque and never interpreted by the generic Grid;
- export is capability-gated and bounded;
- generic packages contain no feature-specific endpoint, permission or scope logic.
