# Phase 0.91 - Admin Translator Resolution

The admin controller can now resolve a catalogue-backed translator using module-owned translation files.

## New service

```text
Zoosper\Core\I18n\TranslationResolver
```

`TranslationResolver` uses `TranslationFileAggregator` and returns an `ArrayTranslator` for a locale.

## Default config

```text
config/i18n.php
```

Initial values:

```php
return [
    'default_locale' => 'en_AU',
    'admin_locale' => 'en_AU',
    'fallback_locale' => 'en_AU',
];
```

## Controller behaviour

`PageAdminController::t()` still honours an injected `TranslatorInterface`. If no translator is injected, it now resolves one through `TranslationResolver` instead of directly constructing `IdentityTranslator`.

This keeps current English output unchanged while making module-owned translation files active in runtime.
