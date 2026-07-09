# Theme and Template Rendering

Phase 0.13 moves the first frontend page rendering out of inline PHP strings and into theme templates.

## New structure

```text
app/zoosper-theme/
  module.php
  config/theme.php
  src/Theme/Theme.php
  src/Theme/ThemeResolver.php
  src/Template/TemplateRenderer.php

themes/default/
  theme.php
  templates/page.php
  assets/css/app.css
```

## Rendering flow

```text
PageRenderer
  -> TemplateRenderer
  -> ThemeResolver
  -> themes/default/templates/page.php
```

## Why PHP templates first?

Plain PHP templates keep the first implementation tiny, dependency-free and easy to inspect. The service layer is intentionally shaped so Zoosper can later swap in a Marko view driver, Latte, Twig or another renderer.

## Next improvements

- per-site theme assignment
- layout templates
- partials/includes
- template overrides by module
- admin UI for theme selection
- Vite/Tailwind asset workflow
```
