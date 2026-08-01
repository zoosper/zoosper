# Phase 3N final Page Grid service cutover

Update the current Page index-controller factory to inject the complete stack:

```text
PageGridCompletePageBuilder
PageGridMutationCoordinator
PageGridExportRequestCoordinator
```

The workspace renderer chain must be:

```text
GridWorkspaceRenderer
 -> GridWorkspacePageSizeDecorator
 -> GridWorkspaceStatusDecorator
 -> GridWorkspaceViewActionsDecorator
```

The page result must use `PageGridNavigationBuilder` and
`GridWorkspaceCompletePageRenderer`. Remove the old index-only Grid renderer
binding once this route is green; do not retain two Page Grid implementations.
