# Renderer integration patch note

When integrating `GridWorkspaceRenderer` into the latest controller/template,
mutation controls must be real POST forms rather than inert buttons. Each form
must include the project's CSRF field and one stable `action` value from
`GridWorkspaceMutationContract`. The renderer remains transport-neutral because
CSRF token generation belongs to the host admin/auth layer.

The GET workspace form remains responsible for filters, selected bookmark,
visible columns and ordering. Save, reset, delete and set-default remain separate
POST operations to avoid state changes through links or GET requests.
