# Phase 1.04 - Admin Context Translator Resolution

This phase wires admin-user locale context into admin translation resolution.

## Runtime chain

```text
PageAdminController::t()
AdminContextTranslatorResolver
AdminUserLocaleResolver
TranslationResolver::forResolution()
TranslatorInterface
```

The fallback translator remains available, but the preferred runtime path now uses the logged-in admin user's locale when the current admin user has a valid locale value.
