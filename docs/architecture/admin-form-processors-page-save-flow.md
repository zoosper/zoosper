# Phase 0.88 - Wire Admin Form Processors into Page Save Flow

Page create/update now runs registered admin form processors before the repository save operation.

## Flow

```text
Request form data
CSRF validation
Admin form processors for page.form
    - success: continue core page save
    - failure: show validation errors and do not save
Core page repository create/update
Flash success
Redirect
```

## Context passed to processors

```text
action: create|update
page: Page|null
user: AdminUser
```

## Why

Third-party modules can now render custom form fields and validate their own submitted fields without editing `PageAdminController`.
