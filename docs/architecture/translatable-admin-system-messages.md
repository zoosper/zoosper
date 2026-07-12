# Phase 0.89 - Translatable Admin System Messages Foundation

Admin system messages now have a translation contract.

## New contracts

```text
Zoosper\Core\I18n\TranslatorInterface
Zoosper\Core\I18n\IdentityTranslator
```

`IdentityTranslator` is a safe fallback. It returns the same English message, while making the controller call sites translation-ready.

## Controller rule

Do not call flash messages with final hard-coded text directly:

```php
$this->flashMessages?->error('Unable to save page.'); // avoid
```

Use the translation helper:

```php
$this->flashMessages?->error($this->t('Unable to save page.'));
```

## Future direction

A later localisation phase can replace `IdentityTranslator` with a locale-aware translator that loads module-owned translation files.
