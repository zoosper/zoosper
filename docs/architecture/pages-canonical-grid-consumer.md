# Pages as the canonical Admin Grid consumer

Pages now renders saved-view management through `GridWorkspaceMutationFormsRenderer` and delegates POST mutations to `PageGridMutationCoordinator`. The Page module owns only its Grid key, definition, data source, route and thin HTTP adapter. Shared toolbar, popup, view persistence and browser behaviour remain in `zoosper-admin-grid`.

The shared popup is constrained by the nearest `data-grid-workspace` boundary rather than the full viewport. Store Orders is not changed by this phase beyond consuming the updated shared browser asset.
