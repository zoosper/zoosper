# Phase 0.91 progress report

## Feature name

Wire Translation Catalogue into Admin Translator Resolution.

## Implemented

- Added `config/i18n.php` with default/admin/fallback locale values.
- Added `TranslationResolver`.
- Updated `PageAdminController` so `t()` resolves a catalogue-backed translator when no translator is injected.
- Preserved injected `TranslatorInterface` support for future DI/container wiring.
- Added verifier for admin translator resolution.

## Why

Phase 0.90 made module-owned translation files discoverable. Phase 0.91 makes those catalogues usable by admin runtime translation, while preserving the current English output and avoiding full locale-selection complexity too early.
