# Admin logout blocked by expired CSRF token

## Symptom

Submitting logout shows:

```text
Your session security token expired
For your protection this action was blocked.
```

## Cause

The admin CSRF middleware validates every state-changing admin request, including `POST /admin/logout`.

The admin navigation rendered a POST logout form, but it did not include the current `_csrf_token` hidden field. That meant the middleware rejected logout before the controller could clear the session.

## Fix

`AdminLayout` now receives `CsrfTokenManager` and renders:

```html
<input type="hidden" name="_csrf_token" value="...">
```

inside the logout form.

## Related note: login history

The provided dump did not include `app/zoosper-admin/src/Controller/LoginController.php` or `app/zoosper-admin/src/Audit/LoginHistoryRepository.php`. This phase fixes the confirmed logout CSRF issue and adds a guard test. If login-history rows still do not update after logout is fixed, dump those two files before patching the history writer.
