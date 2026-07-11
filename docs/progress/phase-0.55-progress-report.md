# Phase 0.55 progress report

## Feature name

Module service provider / DI foundation.

## Implemented

- Added lazy factory support to `ServiceContainer`.
- Added `ServiceProviderLoader` for module-owned `config/services.php` files.
- Updated `ModuleRegistry` to discover custom modules under `modules/<module>` and `modules/<vendor>/<module>`.
- Simplified `ApplicationFactory` so feature services are no longer manually composed there.
- Added service provider files for core, admin, auth, site, theme, page, mail and two-factor modules.
- Updated selected controller provider files to consume registered services instead of constructing service graphs inline.
- Added diagnostics for modules and service providers.

## What remains

- Move more controller-specific factory logic into module service providers as features grow.
- Add interface preference/decorator conventions later.
- Add service provider compilation/cache later for production performance.
- Add module dependency validation and conflict detection later.

## Risks or considerations

- Service IDs can be overridden by later modules; document and use sort_order carefully.
- Business logic must not become service-locator driven.
- This phase changes bootstrap wiring; run all verification and browser regression checks.
