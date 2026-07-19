# Media standalone package readiness

Phase 1.37o prepares `packages/zoosper-media` for eventual extraction into a standalone `zoosper/media` repository.

## Package boundary

The media package now carries the files a future repository needs:

```text
composer.json
module.php
config/
src/
tests/
phpunit.xml.dist
README.md
.github/workflows/tests.yml
.gitignore
```

## Current dependency reality

The package already advertises itself as:

```text
name: zoosper/media
type: zoosper-module
extra.zoosper.module: module.php
```

It still depends on root/first-party packages that are not all independently published yet:

```text
zoosper/core
zoosper/admin
zoosper/auth
```

Until those packages are published separately, standalone development should use local path repositories or root monorepo test execution.

## Why this matters

This gives Zoosper a Magento-style package path:

```text
packages/zoosper-media today
vendor/zoosper/media later
```

without making the media module's business logic depend on root application layout.

## Audit command

The root project now has:

```bash
php8.5 tools/audit-media-standalone-package.php
```

It validates the package metadata, module metadata, source/test directories and standalone-support files.
