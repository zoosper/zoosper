# Zoosper CMS

Zoosper is a brand-new, modern, fast, easy, secure and API-first CMS.

This first skeleton intentionally does only **Hello World**, but it already establishes the architecture direction:

- PHP 8.5+ target
- API-first routing
- admin route placeholder
- role/permission contracts
- secure response headers
- simple module folders
- small dependency-free bootstrap so you can run it immediately
- Composer/PSR-4 ready
- Marko-ready folder layout: `app/`, `modules/`, `config/`, `public/`, `storage/`, `tests/`

> This skeleton does not depend on Marko runtime APIs yet. That is deliberate: it gives you a clean, runnable starting point while preserving a Marko-compatible module structure. In the next phase, we can swap the internal mini-kernel/router for real Marko bootstrapping.

## Quick start

```bash
cd zoosper
php -S 127.0.0.1:8080 -t public
```

Open:

- Frontend: `http://127.0.0.1:8080/`
- Admin placeholder: `http://127.0.0.1:8080/admin`
- API health: `http://127.0.0.1:8080/api/v1/health`
- API hello: `http://127.0.0.1:8080/api/v1/hello`
- API current user placeholder: `http://127.0.0.1:8080/api/v1/me`

## Intended first milestone

This is phase 0.1 skeleton only. It validates:

1. Public entry point works.
2. API route works.
3. Admin route works.
4. Security headers are applied globally.
5. Role/permission contracts exist from the start.
6. Module layout is ready for real functionality.

## Folder structure

```text
app/
  zoosper-core/
  zoosper-api/
  zoosper-admin/
  zoosper-auth/
  zoosper-site/
  zoosper-page/
config/
modules/
public/
storage/
tests/
```

## Next phase suggestion

Phase 0.2 should add:

- SQLite/MySQL database connection
- migrations
- first admin user creation command
- real login/logout
- persistent roles and permissions
- site resolver by hostname
- page entity and page rendering service
```

