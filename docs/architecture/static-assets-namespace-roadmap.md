# Static assets namespace roadmap

## Recommendation

Move framework and module assets away from application route namespaces:

```text
/admin/*
/frontend/*
```

to a static namespace such as:

```text
/assets/admin/...
/assets/frontend/...
/assets/modules/<module>/...
```

## Why

Keeping static assets under `/assets/...` prevents collisions between real filesystem directories and application routes such as `/admin`. It also makes a dynamic admin path easier because changing `ADMIN_PATH` will not require changing static asset URLs.

## Proposed module asset config shape

```php
return [
    'assets' => [
        'zoosper-tag-selector-style' => [
            'type' => 'style',
            'path' => '/assets/admin/css/zoosper-tag-selector.css',
            'sort_order' => 40,
        ],
    ],
];
```

## PCI-aware note

Static asset paths and configs must never contain OTPs, TOTP secrets, recovery codes, session IDs, payment data or customer-sensitive data.
