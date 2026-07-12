# Phase 1.10 - Admin Notice CSS Restoration and Locale Persistence Readiness

This phase restores the visual success styling for admin notices while also checking whether the admin-user locale UI is ready for persistence work.

## Notice styling

The expected success notice markup is:

```html
<div class="notice notice-success">Admin user saved.</div>
```

The CSS restoration adds green-background, border, and readable green text styles for `.notice.notice-success` and `.notice-success`.

## Persistence readiness

The readiness verifier checks that the locale field is present in `UserAdminController`, that submitted locale state is referenced for UI rendering, and that the admin-user repository/source references the `locale` column.
