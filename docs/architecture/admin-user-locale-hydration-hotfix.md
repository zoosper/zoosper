# Phase 1.03.1 - Admin User Locale Hydration Hotfix

Phase 1.03 used a broad search for `class AdminUser`, which matched `AdminUserLocaleResolver` before the concrete auth model.

This hotfix updates the apply and verify tools to locate the concrete model more safely:

```text
basename === AdminUser.php
class AdminUser exact word boundary
not AdminUserLocaleResolver
```

It also improves hydration patching by using a balanced-parenthesis constructor-call parser instead of a brittle non-greedy regex.
