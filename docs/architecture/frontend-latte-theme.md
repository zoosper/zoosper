# Phase 0.62 - Default frontend theme converted to Latte

## Goal

Move the default frontend theme HTML out of PHP templates and into `.latte` templates while keeping PHP files as fallback.

## Implemented templates

```text
themes/default/templates/layout.latte
themes/default/templates/page.latte
app/zoosper-page/resources/views/page/view.latte
```

## Fallback behaviour

PHP templates remain in place. `PageRenderer` now asks for `layout` instead of `layout.php`, allowing `TemplateRenderer` to resolve `layout.latte` first and fall back to `layout.php` if needed.

## Security note

Existing page content is rendered with `|noescape` to preserve current CMS behaviour. Before WYSIWYG/editor work, Zoosper should introduce HTML sanitisation and a safe HTML value model.
