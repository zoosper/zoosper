# Zoosper CMS Documentation

Zoosper is a modern, fast, secure, **true modular** PHP 8.5+ CMS. These Markdown
docs are the single source of truth and will be used to generate a full
documentation **website with examples** - so they are kept current every phase.

> Legend: ✅ available · 🔄 in progress · ⬜ planned

## Getting Started

- ⬜ Installation
- ✅ [Environment variables](configuration/environment-variables.md)
- ⬜ Create your first page

## Architecture

- ✅ [Project structure](architecture/project-structure.md)
- ✅ [Entity save lifecycle](architecture/entity-save-lifecycle.md)
- ✅ [Module service-provider DI](architecture/module-service-provider-di.md)
- ✅ [Declarative schema engine](architecture/declarative-schema-engine.md)
- ✅ [Template engine adapters](architecture/template-engine-adapters.md)
- ✅ [HTML sanitizer foundation](architecture/html-sanitizer-foundation.md)
- ✅ [Foundation consolidation](architecture/foundation-consolidation.md)

## Contributor Guides

- ✅ [Testing guide](contributor/testing-guide.md)
- ✅ [Writing save listeners](contributor/writing-save-listeners.md)
- ✅ [Coding standards: apply-* deprecation](contributor/coding-standards-apply-deprecation.md)
- ✅ [Legacy tooling retirement policy](contributor/legacy-tooling-retirement-policy.md)

## Operations

- ✅ [Module development](operations/module-development.md)
- 🔄 CLI tools
- ✅ Local SMTP with Mailpit (operations/local-smtp-mailpit.md)

## Reference

- ✅ [Roadmap status](roadmap/roadmap-status.md)

---

**Docs discipline:** every phase updates the relevant docs. When a feature lands,
its architecture note, contributor guide, and roadmap status are updated in the
same change - so the future docs site is always one build away.
