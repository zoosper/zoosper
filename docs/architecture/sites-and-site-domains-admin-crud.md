# Sites and Site Domains Admin CRUD

The launch-readiness CMS milestone needs real admin-managed site and domain configuration.

## Site model

```text
id
name
code
status
default_locale
theme_code
created_at
updated_at
```

## Site Domain model

```text
id
site_id
host
path_prefix
is_primary
status
created_at
updated_at
```

## Launch routes

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

## Implementation principle

Use the current Zoosper admin conventions rather than introducing a separate CRUD framework. The source inspection tool exists so the implementation can match current controllers, route config, service registration, templates and permission naming.

## Non-goals

```text
- organisation tenancy
- billing tenancy
- multi-database tenancy
- DNS verification
- Magento website/store/store-view hierarchy
```
