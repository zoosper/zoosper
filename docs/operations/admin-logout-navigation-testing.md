# Admin logout navigation testing

Run syntax and verification checks:

```bash
php -l app/zoosper-admin/src/Layout/AdminLayout.php
php -l tools/verify-admin-logout-navigation.php
php tools/verify-admin-logout-navigation.php
```

Browser test:

1. Log in to admin.
2. Confirm an **Account** group appears in admin navigation.
3. Select **Logout**.
4. Confirm the browser returns to `/admin/login`.
5. Try opening `/admin` again and confirm authentication is required.

If the button appears unstyled, a future admin-theme polish phase can add CSS for `.admin-nav-logout-form` and `.admin-nav-logout-button`.
