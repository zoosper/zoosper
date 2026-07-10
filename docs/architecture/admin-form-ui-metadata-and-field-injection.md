# Admin Form UI Metadata and Field Injection

Phase 0.22 introduces a Magento-inspired, PHP-native form metadata layer.

## Module-owned config

Modules declare form fields in:

```text
app/<module>/config/admin_ui.php
```

## Supported operations

- `fields` defines normal fields.
- `remove` removes an existing field.
- `replace` fully replaces a field definition.
- `inject` adds fields around known positions such as `after.slug`.

## Example

```php
return [
    'admin.pages.form' => [
        'remove' => ['meta_title'],
        'replace' => [
            'content' => ['type' => 'textarea', 'label' => 'Page Body', 'rows' => 18],
        ],
        'inject' => [
            'after.slug' => [
                'seo_score' => ['type' => 'readonly', 'label' => 'SEO Score'],
            ],
        ],
    ],
];
```

## Important principle

The core loader merges metadata, but fields belong to modules. This keeps Zoosper marketplace-module friendly.
