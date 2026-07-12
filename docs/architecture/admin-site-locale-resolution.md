# Phase 0.92 - Admin/Site Locale Resolution Foundation

Zoosper now has a small locale resolution foundation for admin and site scopes.

## New classes

```text
Zoosper\Core\I18n\LocaleResolution
Zoosper\Core\I18n\LocaleResolverInterface
Zoosper\Core\I18n\ConfiguredLocaleResolver
```

## Config keys

```php
return [
    'default_locale' => 'en_AU',
    'admin_locale' => 'en_AU',
    'site_locale' => 'en_AU',
    'fallback_locale' => 'en_AU',
];
```

## Why this is separate from controller wiring

Phase 0.91 already wires catalogue-backed admin translation. Phase 0.92 introduces a reusable resolver contract/value object first, then a later phase can inject or use this resolver inside runtime services without another large controller refactor.
