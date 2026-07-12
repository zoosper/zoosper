# Phase 0.97 - I18n Service Provider Discovery Registration

This phase introduces a service-provider manifest entry for the i18n provider.

## Manifest

```text
config/service_providers.php
```

## Registered provider

```text
Zoosper\Core\I18n\I18nServiceProvider
```

## Why an apply tool is used

The concrete provider discovery point can vary while Zoosper's bootstrap continues to evolve. The apply tool preserves an existing manifest when present, creates one when missing, avoids duplicate provider entries, and writes a backup before modifying an existing file.
