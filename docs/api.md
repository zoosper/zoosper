# API

Zoosper uses versioned API routes and a consistent JSON response envelope. The public health endpoint is `/api/v1/health` and reports service status plus the central CMS version.

Authentication endpoints and protected identity endpoints are available through the API module. API parity with every Admin-managed resource is not yet complete in the alpha line.

API responses must not expose development stack traces or secrets, even when web development diagnostics are enabled elsewhere.


## Authentication and CSRF model

Zoosper API endpoints support two distinct authentication contexts with explicit CSRF security policies:

1. **Stateless Token Authentication (Bearer / Personal Access Tokens):**
   - API clients authenticating via `Authorization: Bearer <token>` operate statelessly without browser session cookies.
   - Because Bearer tokens are not automatically attached by web browsers on cross-origin requests, CSRF validation is not required for Bearer-authenticated requests.

2. **Stateful Session-Based API Interactions:**
   - Any stateful `/api/*` mutations invoked within an authenticated web session (such as AJAX calls or browser-based admin extensions relying on session cookies) must include a valid CSRF token.
   - The CSRF token can be provided either in the `X-CSRF-Token` request header or within the POST payload as `csrf_token`.
   - Requests without a valid CSRF token in session-authenticated contexts are rejected with a 403 Forbidden status (`csrf_invalid`).


## Page publication and revisions

PAT clients can publish or unpublish a Site-owned Page with `pages:publish`, list revisions with `pages:read`, and restore a revision with `pages:write`. The token scope is always intersected with the owner's current Page permission. These routes are stateless, resolve the active Site from the request host, retain pre-mutation revisions, and emit safe audit metadata without bearer secrets or content payloads.


## Menu reads

PAT clients can list request-Site Menus, inspect a Menu and its items, and resolve an active frontend tree with `menus:read` plus the token owner's current `menu.manage` permission. These routes are stateless and return not-found semantics across Site boundaries.


## Menu mutations and lifecycle

PAT clients with `menus:write` and current `menu.manage` can create and update request-Site Menus and items, delete childless items, disable or restore Menus, and permanently delete only inactive empty Menus. Site ownership, same-Menu parent relationships, and published same-Site Page targets are server validated. Audit metadata excludes bearer secrets and Menu content payloads.
