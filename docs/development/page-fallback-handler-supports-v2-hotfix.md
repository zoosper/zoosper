# Page Fallback Handler Supports Hotfix v2

## Issue

`CoreDecouplingContractsTest` still saw `NullFallbackHandler` without `supports()`. The stronger v2 patch overwrites the contract, null handler, new page handler, and the legacy `PageFallbackHandlerAdapter` so all known fallback implementations expose:

```php
supports(object $request): bool
handle(object $request): mixed
```

## Verification

```bash
grep -R "function supports" -n app/zoosper-core/src/Routing app/zoosper-page/src/Routing
php8.5 $(which composer) dump-autoload
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/CoreDecouplingContractsTest.php app/zoosper-core/tests/Unit/Architecture/CoreDecouplingPhase144ClosureTest.php app/zoosper-core/tests/Unit/Architecture/PageFallbackHandlerBoundaryFoundationTest.php
```
