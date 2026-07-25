# Phase 1.69m-z: Guarded ApplicationFactory Fallback Cutover

## Purpose

This phase adds a guarded patcher for the `ApplicationFactory` fallback-boundary cutover.

It replaces the direct Page module dependency:

```php
use Zoosper\Page\Controller\PageController;
$pageController = $services->get(PageController::class);
```

with the core-owned fallback handler boundary:

```php
use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Routing\NullFallbackHandler;

$fallbackHandler = $services->has(FallbackHandlerInterface::class)
    ? $services->get(FallbackHandlerInterface::class)
    : new NullFallbackHandler();
```

## Safety

- Dry-run by default.
- `--apply` required for writes.
- Refuses to apply unless the exact import and lookup are found.
- Writes a backup under `var/backups/application-factory-fallback-cutover/<timestamp>/`.

## Commands

Dry-run:

```bash
php8.5 tools/apply-application-factory-fallback-cutover.php
```

Apply:

```bash
php8.5 tools/apply-application-factory-fallback-cutover.php --apply
```

## Verification after apply

```bash
php8.5 tools/audit-page-fallback-runtime-cutover-readiness.php
php8.5 tools/audit-core-feature-coupling.php
php8.5 tools/plan-core-feature-decoupling-remediation.php
php8.5 $(which composer) dump-autoload
php8.5 vendor/bin/pest
```
