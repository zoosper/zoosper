# URL Rewrites and Dynamic Admin Path

Phase 0.24 introduces the foundation for SEO-friendly frontend URL rewrites and a configurable admin front name.

## URL rewrites

The new URL rewrite module owns:

```text
app/zoosper-url-rewrite/
```

The proposed storage table is:

```text
url_rewrites
```

A frontend request path can be mapped to a target path, entity type or entity ID. This is intended for page aliases and redirects. URL rewrite records must not store payment data or any PCI-sensitive information.

## Dynamic admin path

The new admin routing helpers are:

```text
Zoosper\Admin\Routing\AdminPathResolver
Zoosper\Admin\Routing\AdminUrlGenerator
```

Configuration is stored in:

```text
config/admin.php
```

Environment setting:

```text
ADMIN_PATH=admin
```

## Security note

A hidden admin path is not a complete security control. It may reduce automated scanning noise, but secure authentication, ACL, CSRF protection, secure sessions, audit logging and admin 2FA remain required.
