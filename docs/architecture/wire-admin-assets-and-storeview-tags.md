# Wire Admin Assets and Page Store-view Tags

Phase 0.30 prepares the wiring layer for module-owned admin assets and page store-view tag selection.

## Added integration services

```text
Zoosper\Admin\Asset\AdminAssetRenderer
Zoosper\Page\Form\PageStoreViewOptionsProvider
Zoosper\Page\Service\PageStoreViewAssignmentService
```

## Intended controller flow

The page admin controller should eventually:

1. Load active site/store-view options through `PageStoreViewOptionsProvider`.
2. Load selected IDs through `PageStoreViewAssignmentService::selectedSiteIds()`.
3. Render `partials/components/page/store-view-tags.php` in create/edit forms.
4. Persist selections through `PageStoreViewAssignmentService::saveFromForm()` after page create/update.

## Intended layout flow

The admin layout should eventually receive `AdminAssetRenderer` and print:

```php
$assets->renderStylesheets()
$assets->renderScripts()
```

This keeps module asset declarations owned by each module.

## PCI-aware note

Page store-view selection is relationship metadata only. Do not use tag selector form fields for OTPs, TOTP secrets, recovery-code plaintext, payment data or session tokens.
