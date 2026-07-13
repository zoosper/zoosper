# Phase 1.17.1 - Named Argument Locale Hotfix

Phase 1.17 correctly patched locale persistence signals, but `UserAdminController.php` failed syntax because the apply tool appended a positional locale argument after existing named arguments.

PHP does not allow positional arguments after named arguments.

This hotfix converts the inserted locale argument from:

```php
$this->adminUserLocaleFromForm($form)
```

to:

```php
locale: $this->adminUserLocaleFromForm($form)
```

inside named-argument calls to `createWithRoleIds()` and `updateUser()`.
