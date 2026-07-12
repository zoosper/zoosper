# Phase 0.86 - Module Admin Form Config Aggregation

Zoosper can now aggregate `admin_forms.php` configuration from the project root and module folders.

## Discovery paths

```text
app/*/config/admin_forms.php
modules/*/config/admin_forms.php
modules/*/*/config/admin_forms.php
vendor/*/*/config/admin_forms.php
config/admin_forms.php
```

## Why

This lets core and third-party modules contribute admin sections without editing `PageAdminController` or central root config by hand.

## Current page module contribution

```text
app/zoosper-page/config/admin_forms.php
```

registers providers for:

```text
page.form
```
