# Phase 1.03 - Admin User Locale Hydration Foundation

This phase introduces hydration support for `admin_users.locale`.

## Behaviour

- `AdminUser` receives an optional locale value.
- Admin-user hydration passes the database `locale` column into the model.
- `I18nServiceProvider` registers `AdminUserLocaleResolver`.

The translator runtime is not switched to per-user locale yet; that remains a future phase when an admin-user context is available in translator resolution.
