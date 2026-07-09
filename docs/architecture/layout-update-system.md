# Layout Update System

Zoosper now has the first Magento-inspired layout update foundation.

## Layout update file

Themes can define:

```text
themes/admin/default/layout.php
themes/default/layout.php
```

## Supported operations

```php
return [
    'admin.layout' => [
        'remove' => ['partials/footer.php'],
        'replace' => ['partials/header.php' => 'partials/custom-header.php'],
        'inject' => [
            'before.content' => ['partials/announcement.php'],
        ],
    ],
];
```

## Meaning

- `remove` hides a template/partial.
- `replace` swaps one template for another.
- `inject` renders extra templates into named slots exposed by layout templates.

This is intentionally PHP-array based rather than XML, but conceptually similar to Magento layout customisation.
