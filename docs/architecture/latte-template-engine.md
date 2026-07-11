# Phase 0.60 - Latte template engine integration

## Goal

Add Latte as Zoosper's first modern template engine while preserving PHP templates as a fallback.

## Architecture

```text
TemplateRenderer
  -> TemplateEngineRegistry
      -> LatteTemplateEngine (.latte)
      -> PhpTemplateEngine (.php)
```

Extensionless template names can now resolve to `.latte` or `.php`, depending on available files and registered engines.

## Why Latte

Latte is the recommended first engine because it has PHP-friendly syntax, supports PHP 8.5 via Composer package metadata, and provides a strong security story through context-sensitive escaping.

## Flexibility

Developers are not locked into Latte. A module can still override `TemplateEngineRegistry` or register additional engines through `config/services.php`.
