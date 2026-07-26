# Security foundations

Zoosper defaults toward secure, auditable behaviour suitable for a CMS that will handle admin accounts and eventually commerce-adjacent features.

## Webroot isolation

Only `public/` is web-accessible. Logs, config, vendor, uploads originals, and application code live outside it. See [Project layout](project-layout.md).

## Sessions & CSRF

Admin sessions use configurable cookie name, Secure flag, and SameSite. State-changing admin routes require CSRF tokens validated by middleware. Include `_csrf_token` on POST forms including logout.

See [Routing, middleware & access control](routing-middleware-and-access-control.md).

## Output escaping

- Latte escapes template output by default.
- User-authored titles, slugs, and attributes stay escaped.
- CMS body HTML passes through a sanitiser (`HtmlPurifierSanitizer` recommended) and is rendered through controlled renderer paths — not raw echo of POST data.

## HTML sanitiser scope

The HTML sanitiser is for **CMS rich content only**. Never run OTPs, tokens, passwords, or payment data through it.

## ACL

Permissions are declared per module in `config/acl.php` and enforced on admin routes. Prefer granular permissions over ad hoc checks in templates.

## Secrets & PCI-aware logging

Never log or persist in plain text:

```text
OTP / TOTP secrets
Recovery-code plaintext
Password reset tokens
Session IDs / CSRF tokens
SMTP passwords
Payment card or bank data
```

Store hashes or ciphertext where secrets must be retained (2FA, recovery codes, challenges).

## Rate limiting

Database-backed rate limiting is planned behind `RateLimiterInterface`; treat as roadmap until documented in [Roadmap status](../roadmap/roadmap-status.md).

## Related guides

- [Mail, logging & errors](mail-logging-and-errors.md)
- [Entity save lifecycle](entity-save-lifecycle.md)
