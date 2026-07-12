# Phase 1.11.2 - Post-save Admin User Locale Persistence Hotfix

Previous persistence attempts failed because the save flow did not match assumed submitted-array or constructor patterns.

This phase avoids patching the main save SQL path. Instead, after the existing user save succeeds, the controller calls a small repository method that updates only `admin_users.locale` for the saved user id.

## Flow

```text
existing user save/update
persistAdminUserLocalePreference($user)
AdminUserRepository::updateLocale($id, $locale)
UPDATE admin_users SET locale = :locale WHERE id = :id
```

Blank locale values are normalised to null, preserving configured admin-locale fallback.
