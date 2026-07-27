# Admin Controller Relocation (Phase F1)

## What

PageAdminController, RoleAdminController and UserAdminController moved from
the generic `zoosper-admin` module into their owning domain modules
(`zoosper-page`, `zoosper-auth`), mirroring the existing
`Zoosper\Page\Admin\PageGridRepository` convention. Namespace/`use`/config
changes only — zero business logic touched.

## Why

`zoosper-admin` had accumulated business-domain controllers instead of
staying a thin shell providing shared admin UI chrome (layout, navigation,
asset registry) for feature modules to plug into.

## Two bugs caught before shipping

- RoleAdminController's raw-PHP view path arithmetic would have broken from
  the new location (verified via Python path simulation both before and
  after).
- tools/audit-role-admin-view-ownership.php hardcodes the controller's path
  and is executed by a test asserting zero ownership errors — updated the one
  constant; independently simulated the tool's signal logic against the
  relocated file to confirm zero errors before packaging.

## What did not move

RoleAdminController's 4 raw-PHP view files stay in
`app/zoosper-admin/resources/views/admin/roles/` (not available to relocate
safely); the controller reaches back to them via a documented path. A future
phase can move them once available.
