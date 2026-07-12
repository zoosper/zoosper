# Phase 0.94 - Admin Translator Locale Resolver Integration

Zoosper now has a small integration service that resolves the admin translator through the locale resolver.

## New service

```text
Zoosper\Core\I18n\AdminTranslatorResolver
```

It combines:

```text
ConfiguredLocaleResolver
TranslationResolver
TranslationFileAggregator
TranslationCatalogue
ArrayTranslator
```

## Updated service

```text
Zoosper\Core\I18n\TranslationResolver::forResolution(LocaleResolution $locale)
```

This lets runtime code pass the resolved locale object directly to the translation resolver.
