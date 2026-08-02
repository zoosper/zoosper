# Live Admin Grid drag source-path correction

The module asset route maps `zoosper-admin` to
`app/zoosper-admin/resources/assets`. The bridge URLs were correct after Phase 4ZB,
but the files were stored under `resources/admin`, outside that registered root, so
the resolver returned 404.

The bridge files now live beside the working Grid assets:

- `resources/assets/js/zoosper-grid-column-drag.js`
- `resources/assets/css/zoosper-grid-column-drag.css`

The public URLs remain `/asset/zoosper-admin/{js|css}/...`; no public copy is created.
