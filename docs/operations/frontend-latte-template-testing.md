# Frontend Latte template testing

Run:

```bash
php tools/verify-frontend-latte-templates.php
php tools/diagnose-frontend-template-resolution.php
php tools/verify-template-engine.php
php tools/verify-static-assets.php --theme=default
```

Browser checks:

```text
/home
/about-us if page exists
/admin
/admin/pages
```

Expected behaviour:

```text
Frontend pages render through Latte.
CSS loads from /static/themes/default/assets/css/app.css.
Admin pages remain unchanged.
```
