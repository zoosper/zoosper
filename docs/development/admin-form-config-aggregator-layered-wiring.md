# Phase 1.40q-r: AdminFormConfigAggregator Layered Wiring

## Goal

Move `AdminFormConfigAggregator` from direct `require` assignment loading to the proven admin config layered runtime bridge introduced in Phase 1.40n-p.

## Safety rules

- Patch only when a recognised require-assignment pattern is found.
- Create a `.phase140qr.bak` backup before modifying the target.
- Add a local helper so the first consumer migration is small and reversible.
- Audit the final source to ensure the layered bridge marker/helper exists and direct require assignments are removed.

## Verification

```bash
php8.5 tools/apply-admin-form-config-aggregator-layered-loader.php --dry-run
php8.5 tools/apply-admin-form-config-aggregator-layered-loader.php --apply
php8.5 tools/audit-admin-form-config-aggregator-layered-wiring.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigAggregatorLayeredWiringTest.php
php8.5 vendor/bin/pest
```
