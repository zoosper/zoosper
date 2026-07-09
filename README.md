# Zoosper CMS - Phase 0.2

Zoosper is a brand-new, modern, fast, easy, secure and API-first CMS.

## What is included

- Marko packages in `composer.json`
- SQLite/MySQL-ready PDO connection layer
- Migration runner
- `admin_users`, `admin_roles`, `admin_permissions`, `admin_user_roles`, `admin_role_permissions`
- Admin login/logout with server-side sessions
- CSRF protection for HTML login/logout forms
- API login/logout/me endpoints
- Persistent roles and permissions
- Secure password hashing
- No hardcoded admin user

## Start

```bash
cp .env.example .env
composer install
php bin/zoosper migrate
php bin/zoosper admin:create --email=admin@example.com --password='ChangeMe123!' --name='Admin User'
php -S 127.0.0.1:8080 -t public
```

Routes:

- `/`
- `/admin/login`
- `/admin`
- `GET /api/v1/health`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/me`

API login body:

```json
{"email":"admin@example.com","password":"ChangeMe123!"}
```

## Next phase

Add site resolver and content tables: `sites`, `site_domains`, `pages`, `page_revisions`.
