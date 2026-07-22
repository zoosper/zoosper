# Launch Readiness: Admin Navigation

## Context

The admin sidebar is now the visible contract for what Zoosper CMS can do. Dead links in this navigation undermine confidence and slow real usage.

Current areas that need route completion:

```text
Site Domains
Sites
Settings
```

## Principle

Admin navigation should only link to real routes.

If a feature is not complete yet, the route should still render a safe readiness page explaining what is coming next, rather than using `href="#"`.

## Route integrity rule

Every sidebar anchor should meet one of these conditions:

```text
- It links to a registered admin route.
- It is a POST form action such as logout.
- It is intentionally excluded by a documented test exception.
```

For launch readiness, `href="#"` should be treated as admin navigation drift.

## Recommended protected routes

```text
/admin/sites
/admin/site-domains
/admin/settings
```

## Suggested permissions

Initial simple permission mapping:

```text
/admin/sites        -> site.manage
/admin/site-domains -> site.manage
/admin/settings     -> settings.manage
```

If the permission tree is not ready for `site.manage`, use the nearest existing safe administrative permission temporarily, but document the intended final permission.

## Follow-up phases

After route integrity is locked:

```text
1.37v — Sites and site domains admin CRUD
1.37w — Core settings storage and admin settings UI
1.37x — Site theme assignment and frontend theme validation
1.37y — Dashboard launch readiness checklist
```
