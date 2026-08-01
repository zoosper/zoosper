# Admin Grid workspace progressive enhancement

The Admin Grid workspace remains a server-rendered GET form. Phase 2S adds a
module-owned progressive-enhancement asset layer rather than feature-specific or
inline scripts.

The JavaScript:

- opens and closes Filters and Columns panels with `aria-expanded` updates;
- reorders columns with pointer drag;
- provides Move up and Move down buttons for keyboard operation;
- rewrites `column_order[]` hidden values after every move;
- uses no `innerHTML` and no inline event handlers;
- can be absent without preventing ordinary form submission.

The stylesheet supplies compact toolbar, panel, multiselect, focus, responsive
and reduced-motion treatment. Assets are contributed through the module's
`config/admin_assets.php`, keeping ownership in `zoosper/admin-grid` and allowing
Pages, Audit Log and Login History to share one implementation.

Save/delete/default-view actions still require feature-owned authenticated,
permission-checked and CSRF-protected POST routes. The asset does not transmit a
user ID, grid key, class name or repository identity.
