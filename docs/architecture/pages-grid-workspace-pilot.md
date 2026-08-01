# Pages Grid Workspace pilot

The Pages module now has feature-owned integration seams for the shared Admin
Grid workspace:

- `PageGridQueryState` maps the GET request into canonical state;
- `PageGridWorkspace` resolves the authenticated user's view and renders the
  reusable workspace with fixed `admin.pages` identity;
- `PageGridSiteFilter` creates the readable Site multiselect;
- `PageGridMutationHandler` maps stable actions to the normalised mutation
  service with a server-owned grid key and definition.

The HTTP controller remains responsible for authentication, `page.view` or the
project's equivalent Page-management permission, and CSRF verification before
calling mutation handling. It must never accept a user ID or arbitrary grid key
from the request.

The final controller/template patch should use the resolved state's definition
and criteria for both `GridHtmlRenderer` and `PageGridDataSource`, prepend the
workspace HTML above the grid, redirect POST mutations back to `/admin/pages`,
and preserve Site selections through pagination, sorting and bookmarks.
