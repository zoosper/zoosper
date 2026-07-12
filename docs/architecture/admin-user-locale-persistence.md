# Phase 1.11 - Admin User Locale Persistence

This phase persists the admin-user locale preference from the user form to the `admin_users.locale` column.

## Flow

```text
UserAdminController form field: locale
normaliseAdminLocale()
AdminUser model: locale
AdminUserRepository insert/update writes locale
AdminUserRepository hydration reads locale
AdminContextTranslatorResolver can use locale on next request
```

Empty locale values are saved as `null`, which preserves the configured admin locale fallback.
