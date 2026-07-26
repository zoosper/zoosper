# Zoosper CMS — Developer & operator guide

These pages are the **canonical** documentation for Zoosper. They consolidate architecture, operations, and user-facing notes into **feature-based guides** for CMS operators and PHP developers extending the platform.

Older phase-by-phase files under `docs/architecture/`, `docs/operations/`, `docs/progress/`, and `docs/website/` remain in the tree for history but are **not** the primary reading path.

> **Legend:** ✅ available in current dev branch · 🔄 in active development · ⬜ planned

## Getting oriented

| Guide | Audience |
|-------|----------|
| [Getting started](getting-started.md) | Install, env, first site/page/admin user |
| [Project layout](project-layout.md) | Folders, webroot rules, where modules live |

## Core platform

| Guide | Topics |
|-------|--------|
| [Modularity & modules](modularity-and-modules.md) | Extension points, module folders, overrides |
| [Configuration](configuration.md) | Env vars, layered config, module defaults |
| [Routing, middleware & access control](routing-middleware-and-access-control.md) | Routes, ACL, sessions, CSRF, 2FA foundation |
| [Schema & database](schema-and-database.md) | Declarative `db_schema.php`, migrate commands |

## Admin & content

| Guide | Topics |
|-------|--------|
| [Admin interface](admin-interface.md) | Forms, grids, UI injection, translations |
| [Entity save lifecycle](entity-save-lifecycle.md) | Safe saves, listeners, extension fields |
| [Events & observers](events-and-observers.md) | React after actions (publish, etc.) |
| [Sites, pages & content](sites-pages-and-content.md) | Multi-site, Editor.js, rendering, API |
| [Themes & templates](themes-and-templates.md) | Latte, theme overrides, assets |
| [Media library](media-library.md) | Uploads, storage, public URLs |

## Operations & extension

| Guide | Topics |
|-------|--------|
| [Mail, logging & errors](mail-logging-and-errors.md) | SMTP, mail log, exception logging |
| [Security foundations](security-foundations.md) | Webroot, HTML sanitisation, PCI-aware rules |
| [Composer & marketplace modules](composer-and-marketplace-modules.md) | Vendor packages, `zoosper-module` type |
| [CLI, testing & quality](cli-testing-and-quality.md) | `bin/zoosper`, Pest, verification |

## Strategy & roadmap

- [Why Zoosper](../strategy/why-zoosper.md) — positioning and product bet
- [Roadmap status](../roadmap/roadmap-status.md) — current phase snapshot

## Contributor references (unchanged)

- [Testing guide](../contributor/testing-guide.md)
- [Writing save listeners](../contributor/writing-save-listeners.md)
- [Writing event listeners](../contributor/writing-event-listeners.md)
- [Module generator](../contributor/module-generator.md)
