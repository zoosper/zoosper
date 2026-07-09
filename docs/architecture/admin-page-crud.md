# Admin Page CRUD Architecture

Phase 0.4 adds a simple server-rendered admin page manager.

## Routes

```text
GET  /admin/pages
GET  /admin/pages/create
POST /admin/pages/create
GET  /admin/pages/edit?id=123
POST /admin/pages/edit?id=123
GET  /admin/pages/preview?id=123
POST /admin/pages/publish?id=123
POST /admin/pages/unpublish?id=123
```

## Security rules

- All admin page routes require the `page.manage` permission.
- All state-changing POST routes require a CSRF token.
- All admin output is escaped with `htmlspecialchars()`.
- User-authored page content is previewed through `PageRenderer`, which escapes the content.
- Repository writes use prepared SQL statements.

## Revision behaviour

A new row is inserted into `page_revisions` when:

- a page is created
- page title/content/slug/site is edited

Publishing/unpublishing does not create a revision yet because no page content changed. If we later want full workflow history, we should add an `admin_activity_log` or `page_status_history` table.

## Next improvements

- Add success flash messages.
- Add pagination and search to `/admin/pages`.
- Add a real layout/theme module for admin UI.
- Add page status history/audit log.
- Add preview token support for non-logged-in reviewers.
