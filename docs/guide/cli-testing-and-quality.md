# CLI, testing & quality

## Primary CLI

```bash
php bin/zoosper migrate
php bin/zoosper admin:create --email=... --password=...
php bin/zoosper site:create
php bin/zoosper page:create --title="About" --slug=about
php bin/zoosper make:module Vendor_Module
php bin/zoosper make:command Acme_Blog ReindexPostsCommand --name=blog:posts:reindex --description="Reindex blog posts."
php bin/zoosper help
```

Schema tooling:

```bash
php bin/zoosper-schema validate
php bin/zoosper-schema diff
php bin/zoosper-schema apply
```

### Module console commands

Modules can contribute CLI commands via `config/console.php`:

```php
<?php

declare(strict_types=1);

use Vendor\Blog\Console\ReindexPostsCommand;

return [
    ReindexPostsCommand::class,
];
```

Commands must implement `Zoosper\Core\Console\ConsoleCommandInterface`. For commands with dependencies, register them in `config/services.php` and reference by class-string in `config/console.php`.

Use stable, vendor/module-prefixed names (e.g., `blog:posts:reindex`, `catalog:feeds:export`).

## Verification runner

```bash
bin/verify
```

Runs project verification checks (replacing many one-off scripts over time). Run after substantive changes on the dev branch.

## Quality gate

```bash
php tools/gate.php
```

Checks architecture rules, tool hygiene, and registry integrity.

## Deprecated patterns

- **`tools/apply-*.php` scripts** - Deprecated. Edit source files directly. No one-shot regex-based code-mod scripts.
- **`tools/verify-*.php` scripts** - Replaced by Pest regression tests. Keep temporarily as safety net, convert to Pest, then remove.

See [Legacy tooling retirement policy](../contributor/legacy-tooling-retirement-policy.md) and [Coding standards apply deprecation](../contributor/coding-standards-apply-deprecation.md).

## Pest tests

Tests are **co-located per module**:

```text
app/zoosper-core/tests/Unit/...
app/zoosper-page/tests/Unit/...
modules/my-module/tests/Feature/...
```

```bash
composer test
composer test:unit
composer test:feature
composer test:coverage
```

**Rule:** behavioural changes ship with a Pest test — prefer regression tests over bespoke verify scripts.

Shared base: `Zoosper\Core\Testing\TestCase` in `zoosper-core`.

PHPUnit discovers `app/*/tests/{Unit,Feature}` and `modules/*/tests/{Unit,Feature}` via root `phpunit.xml`.

## Adding tests to a new module

1. Create `tests/Pest.php` and test classes under `tests/Unit` or `tests/Feature`.
2. Add a `composer.json` `autoload-dev` PSR-4 entry for your test namespace.
3. Run `composer dump-autoload`.

Full detail: [Testing guide](../contributor/testing-guide.md).

## Related guides

- [Getting started](getting-started.md)
- [Modularity & modules](modularity-and-modules.md)
