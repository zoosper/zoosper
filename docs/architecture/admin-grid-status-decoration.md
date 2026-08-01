# Admin Grid status decoration

Phase 3J integrates active-view status into the shared workspace instead of
requiring each feature module to concatenate presentation fragments. The base
workspace renderer remains unchanged; `GridWorkspaceStatusDecorator` inserts the
server-resolved status at the start of the existing toolbar.

The decorator uses a stable semantic toolbar marker and fails loudly if that
contract drifts. It never accepts a view name, bookmark state or dirty flag from
the request. Pages changes only its renderer dependency to the decorated renderer,
so Audit Log and Login History can adopt the same composition later.

This deliberately uses two focused production classes rather than copying the
workspace renderer or adding Page-specific HTML, supporting Zoosper's minimal-file
and no-duplicate-subsystem policy.
