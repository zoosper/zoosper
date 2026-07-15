# Why Zoosper?

## Who it's for

PHP developers and agencies who need to **extend a CMS deeply and safely** - add
tables, admin screens, business rules, integrations - and who want that power
**without forking core** and without carrying a heavyweight platform.

## The alternatives (a fair look)

- **WordPress** - unbeatable ecosystem and easy to start, but extension relies on
  a hook-and-filter "soup" over lots of global state, and security/quality is the
  integrator's burden. Deep customisation often fights the platform.
- **Magento** - genuinely *supreme* modularity (modules, DI preferences, plugins,
  observers, layout XML). But it is heavy, complex, slow to boot, and its EAV data
  model is famously hard. Overkill unless you truly need it.
- **Full frameworks (Laravel + a CMS package, etc.)** - superb developer
  experience, but framework-opinionated, and you assemble the "CMS" yourself.
- **Flat-file CMS (e.g. Grav)** - beautifully simple, but limited once you need
  real relational data, roles, or a large content operation.

None of these is "wrong" - they serve different needs. Zoosper is a **deliberate
bet in the gap between them.**

## Zoosper's bet

Take the **modularity discipline of Magento** - modules that own their routes,
controllers, services, schema and admin surface; DI overrides; observers;
extension points - and deliver it in a **lightweight, PHP 8.5-native, API-first,
secure-by-default** package that is genuinely a **joy to extend**.

## The one promise

> **Extend anything without forking core. A module is a folder you drop in.**

## The 60-second mental model

A module is a folder - `app/<name>/` for first-party, or
`modules/<vendor>/<name>/` for third-party - with a `module.php` and a set of
convention-based `config/*.php` files. Each file is an extension point:

```
app/acme-blog/
├── module.php                     # name + enabled
├── src/                           # your PHP (PSR-4 autoloaded)
├── config/
│   ├── services.php               # DI factories (can OVERRIDE core services)
│   ├── controllers.php            # controller factories
│   ├── admin_routes.php           # admin routes
│   ├── api_routes.php             # API routes
│   ├── db_schema.php              # your tables/columns (unified schema engine)
│   ├── admin_menu.php             # admin nav entries
│   ├── acl.php                    # permissions
│   ├── admin_forms.php            # admin form sections + processors
│   ├── admin_ui.php               # add/replace/remove/inject admin form fields
│   ├── entity_save_listeners.php  # hook the entity save lifecycle
│   ├── events.php                 # (1.30) react to any application event
│   ├── logging.php                # your log target
│   └── i18n/{locale}.php          # translations
└── resources/views/…              # Latte templates (theme-overridable)
```

**Drop the folder in -> it works. Delete it -> it's gone.** No core edits.

## What you get out of the box (honest)

- **Admin** with users, roles, granular ACL, and a two-factor foundation.
- **Pages** with block/Editor.js content, SEO metadata, publish workflow.
- **Sites** (multi-site context) and **URL rewrites**.
- **Mail** with an SMTP log.
- **Theming** via Latte with module-template override paths.
- **Declarative schema engine** (validated, snapshot-audited, module-owned).
- **API foundation** and central, redacted exception logging.

## What is NOT there yet (so you can trust us)

- **General application events** for arbitrary "react to anything" - **coming in
  Phase 1.30.**
- **Method plugins/interceptors** (alter behaviour you don't own) - roadmapped.
- **Media / file management module** - roadmapped.
- **Frontend rendering of Editor.js block content** - stored & validated today,
  rendering roadmapped.
- **Config layering** (modules shipping config defaults) - roadmapped.
- **Pretty/SEO URLs in admin** (needs router path params) - roadmapped.

We would rather list these plainly than pretend. The foundation they build on is
already done and tested.

## "Hello, module" - how little it takes

To add a working admin screen backed by its own table, a module ships roughly:

```
app/acme-notes/
├── module.php
├── config/db_schema.php      # notes table
├── config/controllers.php    # NoteAdminController factory
├── config/admin_routes.php   # /admin/notes ...
├── config/admin_menu.php     # "Notes" nav item
├── config/acl.php            # note.manage permission
└── src/Controller/NoteAdminController.php
└── resources/views/admin/notes/*.latte
```

`php bin/zoosper migrate` creates the table; the routes/menu/permission light up;
the screen renders through the shared admin layout. Nothing in core changed.
