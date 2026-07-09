# Admin Theme

Phase 0.16 introduces the first admin theme foundation.

## Structure

```text
themes/admin/default/theme.php
themes/admin/default/templates/layout.php
public/themes/admin/default/assets/css/admin.css
```

`AdminLayout` now renders through the admin theme template when a `TemplateRenderer` is available, with a safe fallback renderer if not explicitly wired.

## Next steps

- move admin component snippets into partial templates
- support admin theme selection from config
- support admin module template overrides
