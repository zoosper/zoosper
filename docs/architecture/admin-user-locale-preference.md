# Phase 1.02 - Admin User Locale Preference Foundation

This phase introduces the schema and resolver foundation for admin-user locale preferences.

## Schema

```text
admin_users.locale VARCHAR(16) NULL
```

`NULL` means the configured admin locale remains active.

## Resolver

```text
Zoosper\Core\I18n\AdminUserLocaleResolver
```

The resolver reads an optional `locale` property/getter from an admin-user object. Only locale codes matching `xx_YY` are accepted.
