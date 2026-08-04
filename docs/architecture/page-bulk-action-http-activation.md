# Page bulk-action HTTP activation

`PageBulkActionController` exposes the server backend through `POST /admin/pages/bulk-action` with route permission `page.manage`. It reuses `SessionGuard::requirePermission()`, `CsrfTokenManager::isValid()`, immutable `Request::form()`, `AdminUser::can()`, `FlashMessageStoreInterface` and `Response::redirect()`.

The trusted actor is constructed only from the authenticated `AdminUser` ID and email. The standard `_csrf_token` form field is mapped into the shared coordinator's internal `_csrf` field. Event dispatcher, audit logger and flash-message store are mandatory factory dependencies.

Successful execution is adapted to a 303 redirect to `/admin/pages`. Controlled failures are flashed and redirect to the same safe application-relative path. The browser manifest remains limited to Export selected until the UI activation phase.
