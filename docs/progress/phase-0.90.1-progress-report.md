# Phase 0.90.1 progress report

## Feature name

Translation File Aggregator Comment Hotfix.

## Implemented

- Replaced unsafe wildcard examples in `TranslationFileAggregator` PHPDoc.
- Preserved the actual runtime glob patterns used by the aggregator.
- Added `verify-translation-file-aggregator-comment-safety.php`.

## Why

The PHPDoc comment contained wildcard path examples such as `app/*/i18n/{locale}.php`. That includes a `/*` sequence inside a docblock, which closes the PHP comment early and causes a parse error. Runtime logic was correct; only the comment needed to be made parser-safe.
