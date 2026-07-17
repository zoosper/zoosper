# Admin session and login-history testing

Run:

```bash
php -l app/zoosper-admin/src/Layout/AdminLayout.php
php -l app/zoosper-admin/config/services.php
vendor/bin/pest app/zoosper-core/tests/Unit/Admin/AdminLayoutLogoutCsrfTest.php
PHP=php8.5 bin/verify
```

Browser checks:

```text
/admin
Click Logout
```

Expected:

```text
Logout form submits without 419.
Session is cleared and the user is returned to the login screen.
```

If login history still does not update, dump these exact files:

```text
app/zoosper-admin/src/Controller/LoginController.php
app/zoosper-admin/src/Audit/LoginHistoryRepository.php
app/zoosper-admin/resources/views/admin/login-history/index.php
```
