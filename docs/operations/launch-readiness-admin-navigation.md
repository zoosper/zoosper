# Launch Readiness Admin Navigation Operations

Run the durable audit:

```bash
php8.5 tools/audit-admin-launch-readiness-navigation.php
```

Run targeted tests:

```bash
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Routing/AdminLaunchReadinessNavigationTest.php
```

Run full verification:

```bash
php8.5 $(which composer) dump-autoload
PHP=php8.5 bin/verify
```

## Cleanup policy

Do not commit temporary root helper tools for one-off navigation replacement or stub scaffolding.

These files should be absent before commit:

```text
tools/apply-admin-launch-readiness-navigation.php
tools/scaffold-admin-launch-readiness-stubs.php
```

The committed artefacts should be:

```text
- durable audit tool
- launch-readiness stubs
- routing/navigation tests
- architecture/operations/roadmap docs
```
