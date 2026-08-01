# Pages Grid HTTP boundary

Phase 2T adds transport-neutral request parsing and a Pages coordinator around
the workspace pilot.

`GridWorkspaceMutationGuard` permits mutations only through POST and only for the
five stable action names. `GridWorkspacePostState` strips client fields outside
the recognised workspace state. In particular, submitted admin user IDs, grid
keys and redirect destinations are ignored.

`PageGridHttpCoordinator` requires the authenticated admin ID as an explicit
argument for both view and mutation paths. It uses the Page-owned fixed grid key
and action URL through the existing `PageGridWorkspace` and
`PageGridMutationHandler`.

The actual Page controller must still perform permission and CSRF verification
using its current project services before calling `mutate()`. This separation
prevents the reusable Admin Grid package from depending on a concrete Auth or
Admin implementation while keeping the security boundary explicit and testable.
