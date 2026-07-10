# Admin Asset Registry

Phase 0.29 introduces a module-owned admin asset registry.

## Purpose

Admin features such as tag selectors, WYSIWYG editors, charts, dashboards and 2FA screens need CSS/JS assets. Those assets should be declared by their owning module instead of being hard-coded into a central layout file.

## Module config

Modules can define:

```text
app/<module>/config/admin_assets.php
```

Example:

```php
return [
    'assets' => [
        'zoosper-tag-selector-script' => [
            'type' => 'script',
            'path' => '/admin/js/zoosper-tag-selector.js',
            'sort_order' => 40,
            'defer' => true,
        ],
    ],
];
```

## PCI-aware note

Asset declarations must not contain OTP values, TOTP secrets, recovery codes, provisioning URIs, payment data or session tokens.
