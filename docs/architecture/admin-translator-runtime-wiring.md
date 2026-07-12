# Phase 0.95 - Wire AdminTranslatorResolver into Admin Runtime

This phase wires the admin controller runtime fallback path to use `AdminTranslatorResolver`.

## Runtime path after wiring

```text
PageAdminController::t()
  -> defaultTranslator()
  -> AdminTranslatorResolver
  -> ConfiguredLocaleResolver
  -> TranslationResolver::forResolution()
  -> TranslationFileAggregator
  -> TranslationCatalogue
  -> ArrayTranslator
```

## Why there is an apply tool

The current controller is actively changing between phases. To avoid shipping a stale full-controller replacement, Phase 0.95 includes an idempotent apply tool that updates only the `defaultTranslator()` wiring and preserves the rest of the current controller file.
