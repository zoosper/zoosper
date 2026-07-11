# Template engine selection

## Default now

```text
php
```

This keeps existing templates compatible.

## Recommended next engine

```text
latte
```

Latte is recommended first because it has PHP-like syntax, strong security positioning and context-sensitive escaping.

## Swapping engines later

A module can override or extend template engines through `config/services.php` by providing a custom `TemplateEngineRegistry` or adding an engine implementation.

## Licence note

Track third-party template engine licences in `docs/licences`. Do not copy vendor source into Zoosper; prefer Composer dependencies with licence notices preserved.
