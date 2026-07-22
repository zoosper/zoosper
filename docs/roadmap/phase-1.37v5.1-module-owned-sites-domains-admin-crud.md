# Phase 1.37v.5.1 — Module-owned Sites and Domains Admin CRUD

## Purpose

Correct Phase 1.37v.5 so Sites and Site Domains admin functionality is owned by `zoosper-site` rather than fattening central `zoosper-admin` route, menu, controller and service configuration.

## Outcome

```text
- Moves SiteAdminController and SiteDomainAdminController under Zoosper\Site\Admin\Controller.
- Moves admin routes to app/zoosper-site/config/admin_routes.php.
- Moves admin menu entries to app/zoosper-site/config/admin_menu.php.
- Moves controller factories and repository services to app/zoosper-site/config.
- Restores central admin route/controller/service ownership.
- Keeps SiteDomainRepository, SiteRepository admin methods and site_domains schema in zoosper-site.
```

## Permission note

The temporary permission remains `settings.manage` because that is the currently available administrative permission. The intended future permission remains `site.manage`.
