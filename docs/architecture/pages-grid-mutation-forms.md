# Pages Grid mutation forms

Phase 2V adds the final server-rendered POST controls required by the modern Grid
Workspace. The renderer receives a CSRF field and token from the host admin/auth
layer and emits separate POST forms for saving/resetting columns, saving a named
view, saving a default view and deleting the active view.

The forms carry only recognised Grid state. They never emit an admin user ID,
grid key, repository class or redirect destination. The action path must be a
local application path. Complete resolved state is included for named views so
filters, Site multiselection, sorting, page size, visible columns and ordering are
persisted together.

`PageGridPresentation` combines the existing GET workspace with these mutation
forms at the fixed Page-owned endpoint `/admin/pages/grid`. The live controller
must supply its current CSRF token, authenticate the administrator, enforce Page
management permission, and route the POST through the established mutation
coordinator before flashing the returned message and redirecting to
`/admin/pages`.
