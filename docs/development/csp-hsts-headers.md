# CSP + HSTS Response Headers

## Content-Security-Policy

Configured in `config/security.php` under `csp`:

- `enabled` - master switch.
- `report_only` - when true, sends `Content-Security-Policy-Report-Only`
  (observe, do not block). When false, sends the enforcing
  `Content-Security-Policy`.
- `policy` - the policy string.

Default ships report-only so it cannot break the admin. Roll out by observing
DevTools CSP reports during a full admin walkthrough, tightening the policy, then
switching to enforce.

Editor.js note: if enforcing breaks the editor due to inline scripts, prefer a
per-request nonce or a hash in `script-src` over `'unsafe-inline'`.

## Strict-Transport-Security

Configured under `hsts`:

- `enabled`, `max_age`, `include_subdomains`, `preload`.
- Emitted ONLY on HTTPS requests (via `Application::requestIsHttps()`), so local
  HTTP dev is unaffected and browsers never receive a meaningless HSTS over HTTP.
- Only set `preload => true` when every subdomain is HTTPS-only and you intend to
  submit to the browser preload list (it is effectively irreversible short-term).

## Testing

`SecurityHeaders::resolvedHeaders()` returns the exact header set without
emitting it, enabling the behavioural tests in
`app/zoosper-core/tests/Unit/Security/SecurityHeadersTest.php`.
