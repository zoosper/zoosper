# Configuration

Zoosper loads configuration from layered sources so modules can ship defaults and projects retain final control.

## Layering model

1. Each **enabled module** may define defaults in `config/settings/*.php` (merged by file name).
2. The project **`config/`** directory at the repository root **always wins** over module defaults.

`ApplicationFactory` builds a single `ConfigRepository` from this merge before database, logging, and routes are wired.

List-shaped config (routes, middleware stacks, menus) uses additive merge semantics appropriate to each loader — when extending those files, follow existing module examples rather than replacing entire arrays blindly.

## Environment variables

Local and deployed values come from `.env` (see `.env.example`). Documented groups include application identity, database, session, default site, assets, admin editor, and SMTP.

Reference: [Environment variables](../configuration/environment-variables.md).

## Supported admin locales

Root or module config can expose human-readable locale choices for admin users:

```php
'supported_admin_locales' => [
    'en_AU' => 'English (Australia)',
],
```

`SupportedLocaleProvider` validates codes before they are used for translation file lookup or persistence.

## Module author tips

- Ship non-secret defaults in `config/settings/<topic>.php`.
- Document new keys in this guide or a module README when you add them.
- Never put SMTP passwords, API keys, or encryption secrets in committed config files.

## Related guides

- [Getting started](getting-started.md)
- [Modularity & modules](modularity-and-modules.md)
