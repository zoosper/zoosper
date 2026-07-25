## Phase 1.68a-l: Page fallback handler boundary foundation

Status: ready to apply

Adds the core-owned fallback handler contract and page-module adapter needed before cutting over `ApplicationFactory` away from a direct Page controller import.

Safety:

- No runtime cutover yet.
- No database changes.
- Core contract does not import the Page module.

Verification gates:

- `php8.5 -l app/zoosper-core/src/Routing/FallbackHandlerInterface.php`
- `php8.5 -l app/zoosper-core/src/Routing/NullFallbackHandler.php`
- `php8.5 -l app/zoosper-page/src/Routing/PageFallbackHandler.php`
- `php8.5 tools/audit-page-fallback-boundary-foundation.php`
- `php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageFallbackHandlerBoundaryFoundationTest.php`
- `php8.5 $(which composer) dump-autoload`
- `php8.5 vendor/bin/pest`
