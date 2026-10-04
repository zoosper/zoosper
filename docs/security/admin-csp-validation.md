# Admin Content Security Policy validation

Zoosper sends an enforcing `Content-Security-Policy` header by default. The Admin policy is same-origin for scripts, connections, forms, frames, and ordinary assets. It does not allow `unsafe-eval`, external script origins, or external style origins.

## Reviewed exceptions

- `style-src 'self' 'unsafe-inline'` remains a narrow compatibility exception. Editor.js 2.31.6 and its bundled tools inject stylesheet elements and set runtime layout styles. The public login, password-reset, TOTP challenge, and CSRF error responses also contain small static style elements. This exception does not permit inline JavaScript and must not be copied to `script-src`.
- `img-src 'self' data:` is required for local image previews produced with `FileReader.readAsDataURL()` before upload.
- `font-src 'self' data:` is retained for bundled font data. No external font origin is approved.
- `blob:` is not approved by the configured policy. The reviewed Admin source does not establish a required user workflow that depends on a blob URL.
- Two `application/json` script elements are non-executable data manifests for Settings scope options and Grid bulk actions. They are explicitly allowlisted by the executable source contract.

## Executable evidence

`AdminCspClosureContractTest` verifies the enforcing header, exact policy boundaries, absence of `unsafe-eval` and external origins, the reviewed style exceptions, and a source scan that rejects unapproved inline executable scripts, inline event handlers, and JSON data-script additions. Existing focused HTTP, feature, and Node DOM suites cover login/password reset, Admin users, Pages, Grid interactions, Settings, Dashboard, Permission Explorer, Personal Access Tokens, the Admin shell, Audit workspaces, Editor insertion, and the Media picker.

These contracts are not a substitute for a real browser CSP run. Production operators must still exercise the complete workflow list in the production operator runbook and treat browser CSP console violations as deployment failures.
