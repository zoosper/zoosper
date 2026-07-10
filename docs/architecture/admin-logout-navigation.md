# Phase 0.50 - Admin logout navigation

## Goal

Restore an obvious logout action in the admin shell while keeping the logout route POST-only.

## Change

`AdminLayout` now appends an **Account** group to the admin navigation with a POST form:

```html
<form method="post" action="/admin/logout">
    <button type="submit">Logout</button>
</form>
```

The action URL is built from `config/admin.php` using `admin.base_path`, so the layout no longer hard-codes `/admin` internally.

## Why POST

The existing logout route is already POST-only. Keeping it this way avoids accidental logout via crawlers, browser prefetch, copied URLs or GET requests.

## Security

The logout navigation does not contain OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or session IDs.
