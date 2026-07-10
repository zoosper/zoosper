# Template Override Path Safety

A runtime warning occurred because an admin theme override used an incorrect relative `require` path:

```text
themes/admin/app/zoosper-theme/resources/views/admin/themes/index.php
```

The Phase 0.20 fix makes the admin theme override self-contained instead of requiring the module view via a fragile relative path.

Rule going forward:

- Prefer resolving module views through `TemplateRenderer`.
- Avoid manual `require dirname(...)` inside theme overrides.
- If duplicated override markup is needed, keep it self-contained and small.
