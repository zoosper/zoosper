# Phase 0.59 - Template engine adapter foundation

## Goal

Make Zoosper template rendering engine-agnostic so developers can choose the best engine for their theme/module while Zoosper ships with a recommended default.

## Architecture

```text
TemplateRenderer
  -> TemplateEngineRegistry
      -> TemplateEngineInterface
          -> PhpTemplateEngine
          -> LatteTemplateEngine future
          -> TwigTemplateEngine future
          -> Custom engine from marketplace/local module
```

## Current state

Phase 0.59 keeps PHP templates working through `PhpTemplateEngine` and prepares the extension point for Latte.

## Why this matters

Zoosper should avoid writing large HTML templates as PHP files where possible. The adapter architecture allows a safe staged migration without breaking the current working frontend/admin templates.
