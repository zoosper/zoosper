# Phase 1.37q.1 - Vendor discovery fixture hotfix

## Goal

Fix the second vendor-package discovery fixture test.

## Diagnosis

The first fixture test passed because it wrote both `vendor/composer/installed.json` and `vendor/composer/installed.php`. The second fixture only wrote `installed.json`, so `ModuleRegistry` did not discover `Acme_HealthData` in the same way Composer would expose installed package metadata.

## Implemented

- Added a shared `createVendorModuleFixture()` helper inside the test file.
- Both fixture cases now create `composer.json`, `module.php`, `config/services.php`, `installed.json`, and `installed.php`.
- The test remains behavioural and still proves vendor-package module discovery outside `app/` and `packages/`.
