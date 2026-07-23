# Phase 1.40s-t: AdminFormConfigAggregator Functional Parity Readiness

## Goal

After Phase 1.40q-r wired `AdminFormConfigAggregator` to the layered runtime bridge, this phase records the aggregator's live constructor/method contract and verifies the source remains ready for a direct fixture-based functional parity test.

## Why this phase is non-mutating

`AdminFormConfigAggregator` may have constructor dependencies or method names that should be discovered from the live repo before a fixture-based functional test is generated. This phase captures that contract without guessing.

## Verification

```bash
php8.5 tools/discover-admin-form-config-aggregator-contract.php
php8.5 tools/audit-admin-form-config-aggregator-functional-parity-readiness.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Config/AdminFormConfigAggregatorFunctionalParityReadinessTest.php
php8.5 vendor/bin/pest
```

## Next phase

Use the generated contract report to build a direct `AdminFormConfigAggregator` fixture parity test that calls the real aggregation method with module and root override fixtures.
