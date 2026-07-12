# Phase 0.90 - Module-owned Translation File Aggregation

Zoosper can now aggregate translation dictionaries from modules without editing core code.

## New classes

```text
Zoosper\Core\I18n\TranslationCatalogue
Zoosper\Core\I18n\TranslationFileAggregator
Zoosper\Core\I18n\ArrayTranslator
```

## Supported translation paths

```text
app/*/i18n/{locale}.php
modules/*/i18n/{locale}.php
modules/*/*/i18n/{locale}.php
vendor/*/*/i18n/{locale}.php
config/i18n/{locale}.php
```

## File format

```php
<?php

declare(strict_types=1);

return [
    'Page saved successfully.' => 'Page saved successfully.',
];
```

## Override rule

Later files override earlier files. Project-level files under `config/i18n` can customise module copy without editing module code.
