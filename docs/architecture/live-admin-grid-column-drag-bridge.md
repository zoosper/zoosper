# Live Admin Grid column-drag bridge

The rendered Pages source proved that `zoosper-admin-grid` package assets were not
emitted into the live admin layout. The page loaded the established
`/asset/zoosper-admin/js/zoosper-grid-columns.js` path but none of the package's new
compact ordering assets. Therefore previous source-level package tests could not make
the browser behaviour change.

This bridge is owned by `zoosper-admin`, whose asset pipeline is proven in the live
HTML. It targets the exact rendered selectors: `[data-grid-column-list]` and
`.grid-compact-column[data-column-key]`. ID and Actions remain locked. A later kernel
asset-discovery fix may retire this bridge once package-owned admin assets are proven
in rendered HTML.
