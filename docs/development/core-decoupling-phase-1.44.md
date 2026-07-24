# Phase 1.44a-c: Core Decoupling Readiness

## Goal

Start the reviewer-recommended decoupling arc by auditing hard modularity violations where `zoosper-core` imports or depends on downstream feature modules.

This phase is intentionally non-invasive. It does not rewire runtime fallback routing, site context resolution, or console command dispatch yet.

## Motivation

The modularity review identified the highest-impact blocker to true modularity: core currently knows about feature modules such as page and site. That prevents disabling/removing non-core modules cleanly.

## Deliverables

- `tools/audit-core-downstream-module-dependencies.php`
- `tools/plan-core-decoupling-phase-144.php`
- `app/zoosper-core/tests/Unit/Architecture/CoreDecouplingReadinessTest.php`
- `docs/development/core-decoupling-phase-1.44.md`

## Planned follow-up sequence

### 1. Fallback route decoupling

Introduce a core-owned fallback handler/provider contract. The page module should register the catch-all fallback instead of core importing page controllers directly.

### 2. Site context decoupling

Introduce a core-owned site context resolver seam. The site module should bind the concrete implementation through its service provider.

### 3. Console kernel decoupling

Keep `bin/zoosper` as a thin kernel and move feature commands into owning modules via module command discovery.

### 4. Admin/API shell decoupling

Longer term, feature-specific admin/API controllers should move from shell modules into owning feature modules.

## Safety rule

Do not remove working concrete wiring until replacement contracts and adapter tests prove equivalent behaviour.
