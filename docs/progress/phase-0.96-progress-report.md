# Phase 0.96 progress report

## Feature name

I18n Service Provider Registration Foundation.

## Implemented

- Added `I18nServiceProvider`.
- Added verifier for i18n service provider registrations.
- Updated `tools/run-verification-suite.php` with the new syntax and verifier commands.

## Why

Phase 0.95 wired admin runtime translation to `AdminTranslatorResolver`. Phase 0.96 prepares the same services for container registration so future phases can inject them instead of constructing resolver services manually.
