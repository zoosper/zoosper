# Module Template Overrides

Phase 0.16 adds module template naming inspired by Marko's `module::path` convention.

## Syntax

```text
zoosper-page::page/view
```

## Resolution order

For a frontend theme `default`, the renderer checks:

```text
themes/default/templates/modules/zoosper-page/page/view
themes/default/templates/modules/zoosper-page/page/view.php
app/zoosper-page/resources/views/page/view
app/zoosper-page/resources/views/page/view.php
```

For non-default themes, the current theme is checked first and the default theme is used as fallback.

## Why this matters

Modules can own their default views under `resources/views`, while themes can override those views under `themes/<theme>/templates/modules/<module>/...`.
