# Phase 1.44d-f: Core Decoupling Contracts

## Goal

Introduce core-owned contracts that allow feature modules to provide behaviour without `zoosper-core` importing their concrete classes.

## Added contracts

- `Zoosper\Core\Routing\FallbackHandlerInterface`
- `Zoosper\Core\Site\SiteContextProviderInterface`

## Added safe defaults

- `Zoosper\Core\Routing\NullFallbackHandler`
- `Zoosper\Core\Site\NullSiteContextProvider`

## Safety model

This phase does not rewire runtime behaviour. Existing fallback routing and site context resolution remain unchanged until later phases add feature-module adapters and explicit wiring tests.

## Next steps

1. Add a page-module fallback adapter that implements `FallbackHandlerInterface`.
2. Add a site-module provider adapter that implements `SiteContextProviderInterface`.
3. Add wiring/config tests before removing core imports of downstream module classes.

## Verification

```bash
php8.5 tools/audit-core-decoupling-contracts.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/CoreDecouplingContractsTest.php
php8.5 vendor/bin/pest
```
