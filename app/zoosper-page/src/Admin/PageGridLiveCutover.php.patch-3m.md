# Phase 3M Pages live cutover checklist

This bulk phase is the point where all prior Grid work becomes visible together.
Update the latest Page controller/template integration to use the completed stack:

1. `PageGridCompletePageBuilder` for GET `/admin/pages`.
2. `PageGridMutationCoordinator` for CSRF-validated POST `/admin/pages/grid`.
3. `PageGridExportRequestCoordinator` for GET `/admin/pages/export`.
4. `GridWorkspacePageSizeDecorator` in the decorated workspace chain.
5. `GridWorkspaceStatusDecorator`, then `GridWorkspaceViewActionsDecorator`.
6. `PageGridNavigationBuilder` for pagination, sorting and export links.
7. `PdoPageGridExportRepository` for bounded resolved-view export.
8. Existing Admin layout renderer for the final complete HTML.

Remove the legacy Page filter/grid block only after the new path serves the live
route. Do not run both implementations or emit two filter forms.

The GET controller must derive pagination metadata from the same
`PaginationResult` used by Grid rendering and pass it to
`GridWorkspacePagination`. The page-size dropdown resets page to one and remains
in saved-view state.
