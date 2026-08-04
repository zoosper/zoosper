# Page bulk-action backend composition

`PageBulkActionBackend` is the production composition root for protected Page bulk actions. It registers server-only Page definitions, the Page-owned executor and mandatory publication event and audit side effects, then creates the shared protected coordinator from host security bindings.

`PageGridBulkActions::serverDefinitions()` is deliberately separate from `definitions()`. The existing browser manifest continues to expose only `export.selected`, while the backend can be composed and tested without making `page.publish` visible or callable from the current Grid UI.

The server definition requires `page.manage`, explicit identities, confirmation, audit readiness and a maximum of 100 selected Pages. Event dispatcher and audit logger dependencies are non-nullable.

This phase does not add an HTTP route or controller. The activation phase must adapt the existing authenticated SessionGuard, CSRF token manager and permission API to `GridBulkHostBindings`, pass a trusted actor context and convert the result to the established Response and flash-message contracts.
