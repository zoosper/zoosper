# Phase 0.98 progress report

## Feature name

Bootstrap Provider Manifest Loader.

## Implemented

- Added `ServiceProviderManifestLoader`.
- Added verifier for manifest loader behaviour.
- Updated `tools/run-verification-suite.php` with the new syntax and verifier commands.

## Why

Phase 0.97 registered `I18nServiceProvider` in `config/service_providers.php`. Phase 0.98 makes that manifest executable through a reusable bootstrap loader.
