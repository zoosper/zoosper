# Zoosper CMS Documentation

Zoosper is a modern, fast, secure, **true modular** PHP 8.5+ CMS. Markdown under `docs/` feeds a future documentation website.

> **Canonical reading path:** [Developer & operator guide](guide/index.md)  
> Legend: ✅ available · 🔄 in progress · ⬜ planned

Historical phase-by-phase notes (architecture/, operations/, progress/, roadmap/, development/, website/) are retained because they are referenced by Pest test files. All current documentation lives in feature-based guides under `docs/guide/`.

## Guide (canonical)

Start here: **[guide/index.md](guide/index.md)**

| Topic | Guide |
|-------|--------|
| Install & env | [getting-started.md](guide/getting-started.md) |
| Folders & webroot | [project-layout.md](guide/project-layout.md) |
| Modules & extension | [modularity-and-modules.md](guide/modularity-and-modules.md) |
| Config & env vars | [configuration.md](guide/configuration.md) · [environment-variables.md](configuration/environment-variables.md) |
| Routes, ACL, CSRF | [routing-middleware-and-access-control.md](guide/routing-middleware-and-access-control.md) |
| Database schema | [schema-and-database.md](guide/schema-and-database.md) |
| Admin UI | [admin-interface.md](guide/admin-interface.md) |
| Save pipeline | [entity-save-lifecycle.md](guide/entity-save-lifecycle.md) |
| Events | [events-and-observers.md](guide/events-and-observers.md) |
| Sites & pages | [sites-pages-and-content.md](guide/sites-pages-and-content.md) |
| Themes & Latte | [themes-and-templates.md](guide/themes-and-templates.md) |
| Media | [media-library.md](guide/media-library.md) |
| Mail & logs | [mail-logging-and-errors.md](guide/mail-logging-and-errors.md) |
| Security | [security-foundations.md](guide/security-foundations.md) |
| Composer modules | [composer-and-marketplace-modules.md](guide/composer-and-marketplace-modules.md) |
| CLI & Pest | [cli-testing-and-quality.md](guide/cli-testing-and-quality.md) |

## Strategy & roadmap

- [Why Zoosper](strategy/why-zoosper.md)
- [Roadmap status](../ROADMAP.md) — master roadmap at project root

## Contributor guides

- [Testing guide](contributor/testing-guide.md)
- [Writing save listeners](contributor/writing-save-listeners.md)
- [Writing event listeners](contributor/writing-event-listeners.md)
- [Module console commands](contributor/module-console-commands.md)
- [Module generator](contributor/module-generator.md)
- [Legacy tooling retirement](contributor/legacy-tooling-retirement-policy.md)
- [Coding standards apply deprecation](contributor/coding-standards-apply-deprecation.md)

## Troubleshooting

Common issues and solutions in [troubleshooting/](troubleshooting/).

## Historical directories

These directories contain phase-by-phase notes and are retained because they are referenced by Pest test files:

```text
architecture/   phase architecture notes and ADRs
operations/     per-phase verification write-ups
progress/       phase progress reports
roadmap/        plans and status fragments
development/    cutover and hygiene notes
website/        early public-doc drafts
```

When a feature changes, update the relevant **`docs/guide/`** page in the same change.
