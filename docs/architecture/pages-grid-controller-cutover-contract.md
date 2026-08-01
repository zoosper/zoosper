# Pages Grid controller cutover contract

Phase 2U closes the remaining framework-neutral boundary before editing the live
Page controller. A Page mutation now produces `GridWorkspaceMutationResult` with
a stable success message and the fixed local `/admin/pages` redirect path.
External and relative redirect targets are rejected.

`PageGridMutationCoordinator` requires an authenticated admin ID and accepts only
a `GridWorkspaceRequest` that has passed the POST/action guard. The host Page
controller remains responsible for the current project's authentication,
Page-management permission and CSRF services before delegation.

The final live-controller integration should:

1. implement or delegate through `PageGridControllerContract`;
2. build the request DTO from the current Request object;
3. derive the admin ID from the existing authenticated session;
4. validate the existing Page-management permission;
5. validate CSRF before `mutateGrid()`;
6. flash the returned message through the current admin message mechanism;
7. redirect only to the returned local path;
8. render workspace HTML above the resolved Grid HTML on GET.

This contract prevents the final patch from introducing client-selected user IDs,
grid keys or redirect destinations while avoiding a second auth/CSRF stack in the
Grid package.
