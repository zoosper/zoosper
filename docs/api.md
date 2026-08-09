# API

Zoosper uses versioned API routes and a consistent JSON response envelope. The public health endpoint is `/api/v1/health` and reports service status plus the central CMS version.

Authentication endpoints and protected identity endpoints are available through the API module. API parity with every Admin-managed resource is not yet complete in the alpha line.

API responses must not expose development stack traces or secrets, even when web development diagnostics are enabled elsewhere.
