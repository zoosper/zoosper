# Phase 0.96 - I18n Service Provider Registration Foundation

This phase adds an i18n service provider that registers translation and locale resolver services with a container-like object.

## New provider

```text
Zoosper\Core\I18n\I18nServiceProvider
```

## Registered services

```text
LocaleResolverInterface
ConfiguredLocaleResolver
TranslationFileAggregator
TranslationResolver
AdminTranslatorResolver
TranslatorInterface
```

## Supported container methods

The provider can register against containers exposing one of these methods:

```text
set
singleton
bind
instance
```

This keeps the i18n layer decoupled from a concrete container implementation while service-provider wiring continues to stabilise.
