# Deferred Near-term Roadmap Items

This file is the durable parking place for important near-term items that are intentionally paused while the Launch Readiness Arc is prioritised.

These items must not be forgotten; they are deferred because they are less critical than making the CMS admin usable for first internal use.

## Deferred while Launch Readiness Arc is active

| Phase | Title | Reason for deferral |
|---|---|---|
| 1.37n.5 | Optional media-gd/media-imagick processor package planning | Media derivative groundwork is sufficient for early CMS use; admin configuration UX is higher priority. |
| 1.38 | RoleAdminController Latte/template migration | Still important for thin-controller/view extraction, but not blocking first CMS configuration usage. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface | Important security hardening, but follows after Sites, Domains, Settings and launch-readiness dashboard work. |

## Resume criteria

Revisit this list after the Launch Readiness Arc has delivered:

```text
1.37u — Admin sidebar route integrity and launch readiness stubs
1.37v — Sites and site domains admin CRUD
1.37w — Core settings storage and admin settings UI
1.37x — Site theme assignment and frontend theme validation
1.37y — Dashboard launch readiness checklist
```


## Deferred Strategic Roadmap Additions

The following items are accepted as useful future direction but are not part of the immediate Phase 1.37 launch-readiness track.

### Versioned Module API

Introduce versioned module API contracts so third-party modules can declare compatible Zoosper platform/module API ranges. This should reduce upgrade risk once the extension ecosystem grows.

### Swappable Driver Interfaces

Formalise swappable drivers for storage, cache, media processing, rendering, search/indexing, queueing, and notification delivery. The media processor interface is the current pilot pattern.

### Workflow, Staging, and Preview

Add workflow and content staging support in a later Phase 2.x track, including draft/scheduled/published states, preview URLs, approval flows, campaign windows, and immutable revisions.

### AI/RAG Extension Hooks

Add optional AI/RAG integration hooks for content embedding, semantic search, editorial assistance, metadata generation, and module-provided indexing pipelines.

### Developer Experience Enhancements

Consider optional TypeScript helper generation and frontend/theme starter kits once admin/editor contracts stabilise.

### Performance Track

Keep framework overhead low and benchmark critical public/admin paths. Resident PHP/server-runner optimisation should be evaluated after correctness, security, and modularity stabilise.

### Optional E-commerce Primitives

Long-term Phase 3.x possibility: catalogue/PIM, inventory, pricing, cart/checkout, and order foundations. This remains optional and should not block CMS launch readiness.

At that point, promote the deferred items back into the active roadmap sequence.

