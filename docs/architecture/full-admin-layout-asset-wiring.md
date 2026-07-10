# Phase 0.32 - Full Admin Layout Asset Wiring

## Goal

Wire module-owned admin assets into the admin layout so modules can contribute CSS/JS through their own config files.

Internal roadmap alignment: Zoosper is intended to be split into Marko-native modules, with a small core and optional features living in installable modules. The roadmap also calls out theme assets and optional modules such as media, SEO, forms, redirects and extensions.

## Added classes

```text
Zoosper\Admin\Asset\AdminAssetViewDataProvider
Zoosper\Admin\Asset\AdminAssetTemplateRenderer
```

## Preferred integration when current layout files are available

1. Register `AdminAssetRegistry` with `ModuleRegistry`.
2. Register `AdminAssetViewDataProvider` or `AdminAssetTemplateRenderer`.
3. Pass `stylesheets` and `scripts` into the admin layout template.
4. Render `partials/components/layout/admin-assets.php`.

## PCI-aware rule

Static asset declarations must not include OTPs, TOTP secrets, recovery-code plaintext, QR/provisioning data, session tokens, payment data or customer-private values.
