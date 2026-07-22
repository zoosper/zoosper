# Sites and Site Domains Admin CRUD

Phase 1.37v moves the Launch Readiness Arc from navigation stubs toward real site configuration.

## Domain model

For the launch-ready CMS milestone, keep the model deliberately simple:

```text
Site
  - id
  - name
  - code
  - status
  - default_locale
  - theme_code
  - created_at
  - updated_at

Site Domain
  - id
  - site_id
  - host
  - path_prefix
  - is_primary
  - status
  - created_at
  - updated_at
```

Do not introduce organisation tenancy, billing tenancy, multi-database tenancy or Magento-style website/store/store-view depth yet.

## Admin routes

```text
/admin/sites
/admin/sites/create
/admin/sites/edit
/admin/site-domains
/admin/site-domains/create
/admin/site-domains/edit
```

## Permissions

Initial intended permissions:

```text
site.manage
```

If the current ACL tree cannot yet express `site.manage`, use the nearest existing administrative permission temporarily and document the follow-up parity fix.

## UX target

`/admin/sites` should list sites and allow create/edit.

`/admin/site-domains` should list host/path mappings and allow create/edit.

Phase 1.37v should prioritise:

```text
- no dead routes
- safe forms
- validation
- persistence
- green verification
```

Advanced multisite modelling can come later.
