# Phase 0.52 progress report

## Feature name

Site/store-view context foundation.

## Implemented

- Added file/env-based site/store/store-view context configuration.
- Added request host/path based context resolver.
- Added request-scoped `CurrentSiteContext` wrapper.
- Added `dynamicForContext()` to CDN resolver so feature code no longer needs to hard-code store codes.
- Added site context diagnostics and verification tools.
- Added architecture and operations documentation.

## What remains

- Register site context services in the shared service container once latest `ApplicationFactory.php` is exported after this phase.
- Update page/theme/media renderers to consume `CurrentSiteContext` and `CdnUrlResolver::dynamicForContext()`.
- Add database-backed website/store/store-view tables and admin UI later.

## Risks / considerations

- Incorrect domain/path config can resolve the wrong store view. Diagnostics help verify host/path behaviour.
- JSON env config can become hard to maintain at scale; database administration should follow later.
- CDN media/static URLs remain global in this phase; website/store-view overrides can be added later if required.
