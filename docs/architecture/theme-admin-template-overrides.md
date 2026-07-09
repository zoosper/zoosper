# Theme Admin and Template Overrides

Phase 0.15 adds the first theme admin screen and template override discovery.

## Theme admin

```text
/admin/themes
```

The screen lists installed themes from:

```text
themes/*/theme.php
```

It also allows assigning a `theme_code` to each active site.

## Template override order

`TemplateRenderer` resolves templates in this order:

1. `themes/<theme>/templates/overrides/<template>`
2. `themes/<theme>/templates/<template>`
3. `themes/default/templates/overrides/<template>`
4. `themes/default/templates/<template>`

This gives themes a way to override templates while keeping default fallback behaviour.
