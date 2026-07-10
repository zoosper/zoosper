# Phase 0.32 Full Admin Layout Asset Wiring - Full Replacements

This package wires module-owned admin assets into the current admin bootstrap/layout files.

## Replaced files

```text
app/zoosper-core/src/Bootstrap/ApplicationFactory.php
app/zoosper-admin/src/Layout/AdminLayout.php
app/zoosper-admin/src/UI/AdminViewRenderer.php
themes/admin/default/templates/layout.php
```

## Behaviour

- `ApplicationFactory` registers `AdminAssetRegistry`, `AdminAssetViewDataProvider`, `AdminAssetTemplateRenderer` and `AssetPathResolver`.
- `AdminLayout` passes discovered stylesheet/script data into `layout.php`.
- `layout.php` renders module-owned assets in the document head/body end.
- `AdminViewRenderer` remains focused on rendering the inner admin template and delegating shell rendering to `AdminLayout`.

## PCI-aware rule

Asset config and rendered asset paths must never include OTPs, TOTP secrets, recovery-code plaintext, QR/provisioning data, session tokens, payment data or customer-private values.
