# Phase 1.13 - AdminUser Field Definition Provider and Write Map

This phase adds a concrete field-definition provider for AdminUser save flows.

## Purpose

AdminUser fields are now declared rather than implied by controller code or raw POST values. The provider marks `name`, `email`, `status`, and `locale` as core columns, while password and role assignment are handler fields and CSRF is virtual.

Third-party providers can be passed to `AdminUserFieldRegistryFactory` to add module-owned extension fields without touching core code.
