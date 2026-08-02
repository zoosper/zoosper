# Live Admin Grid drag asset URL correction

The rendered Pages HTML demonstrated that relative manifest paths such as
`resources/admin/js/zoosper-grid-column-drag.js` were emitted relative to the current
`/admin/pages` URL, becoming `/admin/resources/...` and returning 404.

The Admin module already serves its established Grid assets through the absolute
`/asset/zoosper-admin/{css|js}/...` route. The drag bridge now uses the same absolute
module asset URL convention while its source files remain under the Admin module's
`resources/admin` tree.
