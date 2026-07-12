# Phase 0.94 progress report

## Feature name

Admin Translator Locale Resolver Integration.

## Implemented

- Added `AdminTranslatorResolver`.
- Added `TranslationResolver::forResolution()`.
- Added verifier for admin translator locale-resolver integration.
- Updated `tools/run-verification-suite.php` with the new syntax and verifier commands.

## Why

Phase 0.92 introduced locale resolution. Phase 0.94 connects locale resolution with catalogue-backed translation through a reusable runtime service, without forcing another controller refactor yet.
