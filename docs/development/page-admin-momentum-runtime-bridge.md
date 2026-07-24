# Phase 1.47a-l: Page Admin Momentum Runtime Bridge

## Goal

Add a small page-module bridge that can normalise disabled page momentum route/menu metadata for future live admin integration.

## Added classes

- `Zoosper\Page\Admin\PageMomentumRouteDefinitionProvider`
- `Zoosper\Page\Admin\PageMomentumMenuDefinitionProvider`
- `Zoosper\Page\Admin\PageMomentumRuntimeBridge`

## Safety model

- Metadata remains disabled by default.
- Disabled metadata exports no route/menu definitions.
- Fixture-enabled metadata proves the bridge can export valid definitions.
- The bridge does not register routes or menu entries.
- Live route/menu cutover remains a later phase.

## Verification

```bash
php8.5 tools/prove-page-admin-momentum-runtime-bridge.php
php8.5 tools/audit-page-admin-momentum-runtime-bridge.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Architecture/PageAdminMomentumRuntimeBridgeTest.php
php8.5 vendor/bin/pest
```
