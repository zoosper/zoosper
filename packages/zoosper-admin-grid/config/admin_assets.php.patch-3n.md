# Phase 3N complete Grid asset bundle

Ensure the Admin Grid manifest includes all live workspace assets in this order:

```text
grid-workspace.css
grid-workspace-status.css
grid-workspace-view-actions.css
grid-workspace-live.css

grid-workspace.js
grid-workspace-view-actions.js
grid-workspace-page-size.js
```

Register `grid-workspace-live.css` after the component styles. All scripts remain
module-owned and deferred. The rendered Pages HTML must contain these assets after
cache clear/compile; the attached current HTML contains only the legacy
`zoosper-grid.css` asset, so it does not yet prove the cutover.
