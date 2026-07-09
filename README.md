# Zoosper CMS - Phase 0.4

This phase adds the first real CMS capability:

- site/domain resolver
- persistent sites and domains
- persistent pages
- page revisions
- frontend page rendering by slug
- API endpoint for page lookup
- CLI commands for creating sites and pages
- Marko/Claude docs and `.claude` configuration

The code is intentionally readable and not compressed. Classes are small and explicit so Claude, Copilot and PHPStorm can work with it easily.

## Install locally

```bash
cp .env.example .env
composer install
php bin/zoosper migrate
php bin/zoosper admin:create --email=admin@example.com --password='ChangeMe123!' --name='Admin User'
php bin/zoosper site:create --code=main --name='Main Website' --host=127.0.0.1
php bin/zoosper page:create --site=main --title='Home' --slug=home --content='Welcome to Zoosper.'
php -S 127.0.0.1:8080 -t public
```

Open:

- `/`
- `/home`
- `/admin/login`
- `/api/v1/health`
- `/api/v1/content/page?slug=home`

## What is intentionally still simple

This phase does not yet include a rich admin page editor. Pages can be created from CLI and read via frontend/API. The next phase should add admin page CRUD.

## Claude / Marko files

This phase adds:

- `AGENTS.md`
- `CLAUDE.md`
- `.claude/settings.json`
- `.claude/commands/zoosper-next-phase.md`
- `.claude/commands/zoosper-review.md`
- `docs/architecture/site-and-page-rendering.md`
- `docs/roadmap/phase-0.4-admin-page-crud.md`

These are modelled on the Marko devai guidance: shared instructions in `AGENTS.md`, Claude-specific entrypoint in `CLAUDE.md`, and Claude Code settings in `.claude/settings.json`.
