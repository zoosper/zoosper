# Admin session security

Admin state-changing routes are protected by the central admin CSRF middleware. Controllers and layouts must therefore render `_csrf_token` fields on POST forms.

## Logout

`POST /admin/logout` is intentionally protected by authentication and CSRF middleware. The admin navigation logout form must include the current session CSRF token so an authenticated user can log out successfully without weakening the CSRF policy.

## Policy

- Do not convert logout to GET.
- Do not exempt logout from CSRF unless there is a documented security decision.
- Prefer rendering the same CSRF token field used by the rest of admin forms.
- Do not log session IDs, CSRF tokens, OTPs, TOTP secrets, reset tokens or recovery-code plaintext.
