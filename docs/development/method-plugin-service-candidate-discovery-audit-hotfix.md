# Phase 1.42a-c Audit Autoload Hotfix

## Issue

`tools/audit-method-plugin-service-candidate-discovery.php` checked `Zoosper\Core\Plugin\MethodPluginRuntimeConfig` without loading Composer autoload first, so the audit could report the class as missing even though Phase 1.41 closure proved it exists.

## Fix

The audit now requires `vendor/autoload.php` before calling `class_exists()` and reading the default disabled runtime config.

## Verification

```bash
php8.5 tools/audit-method-plugin-service-candidate-discovery.php
```

Expected:

```text
Zoosper\Core\Plugin\MethodPluginRuntimeConfig: yes
Errors: 0
```
