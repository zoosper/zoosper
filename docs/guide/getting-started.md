# Getting started

Zoosper is a PHP 8.5+ modular CMS. This guide covers a minimal local setup for developers and operators.

## Requirements

- PHP 8.5+ with `ext-pdo`
- Composer
- SQLite (default local) or MySQL (recommended for production-style testing)

## Install

```bash
composer install
cp .env.example .env
# Edit .env: APP_KEY, database, mail as needed
php bin/zoosper migrate
```

Create bootstrap data:

```bash
php bin/zoosper admin:create --email=admin@example.test --password=ChangeMeNow!
php bin/zoosper site:create
php bin/zoosper page:create --title="Home" --slug=home
```

## Run the app

```bash
composer serve
```

Open `http://127.0.0.1:8080` for the frontend and sign in to admin (path depends on your URL rewrite / admin configuration; default patterns use `/admin/...`).

## Environment essentials

Copy values from `.env.example`. Important groups:

| Group | Purpose |
|-------|---------|
| `APP_*` | Name, debug, URL, `APP_KEY` |
| `DB_*` | SQLite file or MySQL connection |
| `SESSION_*` | Cookie name, secure flag, SameSite |
| `DEFAULT_SITE_*` | Default site code, name, host for CLI |
| `ASSET_*` | Static asset base paths (keep separate from `/admin` routes) |
| `ADMIN_WYSIWYG_*` | Editor.js toggle and storage format |
| `MAIL_*` / `SMTP_*` | Outbound mail (see [Mail, logging & errors](mail-logging-and-errors.md)) |

Full variable notes: [Configuration](configuration.md) and [Environment variables](../configuration/environment-variables.md).

## Security habits from day one

- Never commit real SMTP passwords or production secrets.
- Set a strong `APP_KEY` before any shared environment.
- Use MySQL in production; SQLite is for convenient local dev when enabled in config.
- Do not log OTPs, TOTP secrets, recovery codes, reset tokens, or session/CSRF values.

## Next steps

- [Project layout](project-layout.md)
- [Modularity & modules](modularity-and-modules.md) — how to extend without forking core
- [CLI, testing & quality](cli-testing-and-quality.md)
