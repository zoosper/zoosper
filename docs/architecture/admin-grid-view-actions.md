# Admin Grid view actions

Phase 3K adds context-aware saved-view controls. Default workspace offers Save new
view. A selected saved view is prefilled and offers Update view, Make default when
needed, and Delete view. When persistent state differs, Update view receives
primary emphasis.

Action availability is resolved from user-scoped bookmark data and deterministic
dirty state. View names are escaped. This layer does not create HTTP endpoints or
bypass CSRF; it is presentation for the existing protected mutation actions.
Destructive action remains visually distinct and is absent for Default view.
