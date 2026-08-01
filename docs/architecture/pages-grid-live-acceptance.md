# Pages Grid live acceptance

The captured Pages response still represents the legacy Grid path: it contains a
text Site ID filter, a hidden page-size value, the plain Grid table and legacy
pagination. It does not contain workspace, saved-view, configurable-column or
export markers.

Phase 3N makes the cutover testable. `PageGridLiveMarkupContract` defines the
minimum complete-page markers and rejects the known retired controls. A feature
test should run this contract against the real authenticated `/admin/pages`
response after route compilation.

The final cutover must replace, not layer over, the legacy Page Grid. The complete
asset bundle is registered by Admin Grid, while Page owns route, controller,
repository and permission integration. This keeps one source of truth and avoids
leaving an invisible second implementation.
