# Phase 1.01 - Reduce Manual Admin Translator Fallback

Phase 1.00 proved `TranslatorInterface` can be registered in the container and passed into `PageAdminController`.

This phase reduces the manual controller fallback from a full catalogue resolver to a lightweight `IdentityTranslator` safety net.

## Desired runtime path

```text
ServiceProviderManifestLoader
I18nServiceProvider
TranslatorInterface
PageAdminController constructor
PageAdminController::t()
```

## Safety fallback

If the translator is not injected, the controller still falls back to `IdentityTranslator`, which preserves messages and parameters without doing catalogue lookup.
