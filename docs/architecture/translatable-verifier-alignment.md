# Translatable verifier alignment

After Phase 0.91, `PageAdminController` should no longer directly construct or import `IdentityTranslator` for its fallback path.

The valid fallback path is now:

```text
PageAdminController::t()
  -> defaultTranslator()
  -> TranslationResolver
  -> TranslationFileAggregator
  -> TranslationCatalogue
  -> ArrayTranslator
```

The verifier must therefore validate both:

1. `IdentityTranslator` still exists as a safe contract implementation.
2. `PageAdminController` now uses catalogue-backed runtime resolution.
