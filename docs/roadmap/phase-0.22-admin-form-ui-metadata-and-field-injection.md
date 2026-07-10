# Phase 0.22 - Admin Form UI Metadata and Field Injection

Future goal: allow modules or child themes to inject, remove or replace admin form fields in a Magento-like way.

Example direction:

```php
return [
    'admin.pages.form' => [
        'remove' => ['meta_title'],
        'replace' => [
            'content' => ['type' => 'textarea', 'rows' => 18],
        ],
        'inject' => [
            'after.slug' => [
                'seo_score' => ['type' => 'readonly', 'label' => 'SEO Score'],
            ],
        ],
    ],
];
```

This should be implemented as PHP config, not XML, while keeping Magento-like concepts.
