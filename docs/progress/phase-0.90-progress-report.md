# Phase 0.90 progress report

## Feature name

Module-owned Translation File Aggregation.

## Implemented

- Added `TranslationCatalogue`.
- Added `TranslationFileAggregator`.
- Added `ArrayTranslator`.
- Added module-owned admin translation file at `app/zoosper-admin/i18n/en_AU.php`.
- Added verifier for module-owned translation aggregation.

## Why

Phase 0.89 made messages translation-ready. This phase lets modules own translation files so future localisation does not require editing core controllers, files, or central dictionaries.
