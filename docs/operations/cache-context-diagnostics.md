# Cache context diagnostics

Run:

```bash
php tools/verify-cache-context.php
php tools/diagnose-cache-context.php --host=zoosper.lowes.com.au --path=/about-us --theme=default --route=frontend.page
```

Expected output includes context dimensions and generated cache keys for:

```text
page
menu block
public AJAX fragment
private AJAX fragment
```

## Provider agnostic CDN usage

The generated HTTP cache policies are standard HTTP headers. They can be used behind Fastly, Cloudflare, MaxCDN-style services, browser cache, reverse proxies or another CDN later.

## Safety rules

- Public page/fragment responses must not include user-specific data.
- Private fragments should use `private, no-cache` or `no-store` depending on sensitivity.
- OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data and customer-private values must be `no-store`.
