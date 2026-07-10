# Phase 0.24 - URL Rewrites and Dynamic Admin Path

## SEO-friendly URLs

Zoosper should add a `url_rewrites` or `url_mapping` table for frontend URL aliases and redirects.

Suggested fields:

```text
id
site_id
request_path
target_path
entity_type
entity_id
redirect_type
is_active
created_at
updated_at
```

Suggested behaviour:

- resolve frontend request path before normal page slug lookup
- support page aliases
- support 301/302 redirects
- keep admin grid filters as query strings unless there is a strong usability reason not to
- never store payment or sensitive PCI data in the rewrite table

## Dynamic admin path

Replace hard-coded `/admin` with an admin route path configured by environment/config.

Suggested config:

```text
ADMIN_PATH=admin
```

Suggested service:

```text
Zoosper\Admin\Routing\AdminPathResolver
Zoosper\Admin\Routing\AdminUrlGenerator
```

Expected follow-up:

- route loaders use the configured admin path
- admin menus generate URLs through AdminUrlGenerator
- templates do not hard-code `/admin`
- changing the URL is not treated as security by itself; use ACL, CSRF, session hardening and 2FA

## PCI-aware note

Do not rely on a hidden admin path as a security control. It can reduce noise, but PCI-aware security planning should prioritise strong authentication, 2FA, access control, session management and audit logging.
