# Phase 1.68a-l: Page Fallback Handler Boundary Foundation

## Purpose

This phase starts implementing the Page fallback handler boundary identified by the core-feature decoupling remediation plan.

The goal is to move toward this shape:

```text
core -> FallbackHandlerInterface -> page-module implementation
```

instead of:

```text
core -> Zoosper\Page\Controller\PageController
```

## What this phase adds

- `Zoosper\Core\Routing\FallbackHandlerInterface`
- `Zoosper\Core\Routing\NullFallbackHandler`
- `Zoosper\Page\Routing\PageFallbackHandler`
- tests and audit for the foundation

## What this phase does not do yet

- It does not rewrite `ApplicationFactory`.
- It does not remove the existing `PageController` import yet.
- It does not change runtime routing behaviour.

## Next phase

The next phase should cut over `ApplicationFactory` to resolve/use the core-owned fallback handler boundary instead of importing the Page controller directly.
