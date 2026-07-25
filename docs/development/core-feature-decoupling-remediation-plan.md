# Phase 1.67m-z: Core Feature Decoupling Remediation Plan

## Purpose

This phase follows the core-feature coupling audit with a read-only remediation planner.

The planner reads:

```text
var/reports/core-feature-coupling.json
```

and writes:

```text
var/reports/core-feature-decoupling-remediation-plan.txt
var/reports/core-feature-decoupling-remediation-plan.json
```

## Output groups

The planner groups findings by module and suggested boundary type, including:

- fallback handler boundary
- site context boundary
- authentication boundary
- theme resolution boundary
- media service boundary
- general module contract boundary

## Safety

This is read-only planning. It does not edit runtime files.

## Next phase

The next implementation phase should pick the smallest high-value boundary from the plan, most likely the Page fallback handler or Site context boundary, and replace direct core imports with core-owned contracts plus feature-module bindings.
