# Composer module package development

## Module autoload automation

Composer runs Zoosper's module autoload sync through the root `pre-autoload-dump` script before generating autoload files.

```bash
PHP=php8.5 composer dump-autoload
```

You can also run specific commands:

```bash
composer modules:autoload
composer modules:autoload-verify
composer modules:package-audit
```

## Package readiness audit

The audit recognises both module naming styles:

```text
Zoosper_Media
zoosper-media
zoosper-two-factor
```

It exits with status 0 so it can be used as an informational Composer script. Review items are shown in the report rather than breaking the shell flow.
