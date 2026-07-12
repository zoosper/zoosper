# Phase 1.00 - Admin Translator Container Injection

This phase moves the normal admin translation path from manual controller fallback construction towards container-provided `TranslatorInterface` injection.

## Key changes

- `I18nServiceProvider` now uses `ServiceContainer::factory()` when available so it registers lazy services correctly.
- `ApplicationFactory` should load `config/service_providers.php` before `ControllerProviderLoader` creates controller factories.
- `app/zoosper-page/config/controllers.php` passes `TranslatorInterface` into `PageAdminController` when the service is available.

The controller fallback remains in place for safety.
