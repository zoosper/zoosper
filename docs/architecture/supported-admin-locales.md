# Phase 1.05 - Supported Admin Locales Foundation

This phase adds a config-driven list of locales that can later be used by the admin-user locale preference UI.

## Config

```php
'supported_admin_locales' => [
    'en_AU' => 'English (Australia)',
]
```

## Provider

```text
Zoosper\Core\I18n\SupportedLocaleProvider
```

The provider validates locale codes before exposing them, so unsafe values cannot be used for translation-file lookup.
