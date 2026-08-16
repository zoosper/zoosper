# API

Zoosper uses versioned API routes and a consistent JSON response envelope. The public health endpoint is `/api/v1/health` and reports service status plus the central CMS version.

Authentication endpoints and protected identity endpoints are available through the API module. API parity with every Admin-managed resource is not yet complete in the alpha line.

API responses must not expose development stack traces or secrets, even when web development diagnostics are enabled elsewhere.


## Page publication and revisions

PAT clients can publish or unpublish a Site-owned Page with `pages:publish`, list revisions with `pages:read`, and restore a revision with `pages:write`. The token scope is always intersected with the owner's current Page permission. These routes are stateless, resolve the active Site from the request host, retain pre-mutation revisions, and emit safe audit metadata without bearer secrets or content payloads.
