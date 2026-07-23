# Phase 1.40n-p PHP 8.5 Deprecation Note

PHP 8.5 reports `ReflectionProperty::setAccessible()` as deprecated because it has no effect since PHP 8.1. The admin config layered runtime bridge no longer calls `setAccessible()` when reading the `LayeredConfigResult::$config` payload.

Verification should produce no `DEPR` output for `AdminConfigLayeredFileLoader`.
