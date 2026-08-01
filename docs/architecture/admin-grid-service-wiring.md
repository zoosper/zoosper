# Admin Grid service wiring

Phase 3A supplies additive service and route integration patches for the current
manifests without replacing files that may have evolved since the source bundle.

Admin Grid owns generic export, audit-selection and mutation services. Admin
provides only the bridge to the existing audit logger. Page composes the complete
workspace, mutation and export coordinators using fixed Page-owned identities.

The route contract is GET `/admin/pages`, POST `/admin/pages/grid`, and GET
`/admin/pages/export`. The host must preserve its existing auth, permission and
CSRF metadata while merging the routes. No generic user, Grid, repository or
class selector is introduced.

These patch files are intentionally reviewable integration instructions. They
avoid overwriting the latest large service/route manifests with stale snapshots,
while architecture tests lock in dependency direction and endpoint policy.
