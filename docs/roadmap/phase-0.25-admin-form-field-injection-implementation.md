# Phase 0.25 - Admin Form Field Injection Implementation

Phase 0.22 introduced form metadata. A later implementation phase should:

- update `PageAdminController` to render fields from `AdminFormUiConfigLoader`
- update `UserAdminController` to render fields from `AdminFormUiConfigLoader`
- update `RoleAdminController` to render fields from `AdminFormUiConfigLoader`
- support field placement with before/after positions
- support child theme field templates
- add validation metadata support
```
