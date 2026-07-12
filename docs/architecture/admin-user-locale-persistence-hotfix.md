# Phase 1.11.1 - Admin User Locale Persistence Hotfix

Phase 1.11 failed because the controller does not expose the submitted values through the exact array pattern the apply tool expected.

This hotfix patches the save flow more directly:

```php
locale: $this->normaliseAdminLocale($_POST['locale'] ?? null)
```

inside the `AdminUser` construction used by `UserAdminController`, then patches repository insert/update SQL to write `locale` from `AdminUser::$locale`.
