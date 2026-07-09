# Phase 0.4 - Admin Page CRUD Roadmap

The next implementation phase should add admin-managed pages.

## Routes

- `GET /admin/pages`
- `GET /admin/pages/create`
- `POST /admin/pages/create`
- `GET /admin/pages/edit?id=123`
- `POST /admin/pages/edit?id=123`
- `POST /admin/pages/publish?id=123`
- `POST /admin/pages/unpublish?id=123`

## Requirements

- Require `page.manage` permission.
- Use CSRF tokens for all POST routes.
- Create a revision when content changes.
- Escape all admin output.
- Keep repository SQL parameterised.
- Add API endpoints after server-side admin CRUD is stable.
