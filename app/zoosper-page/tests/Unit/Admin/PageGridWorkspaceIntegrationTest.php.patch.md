# Update the expected canonical query state

Add the two new empty column-specific filters after `q`:

```php
'filters' => [
    'q' => 'landing',
    'title' => '',
    'slug' => '',
    'status' => 'published',
    'site_id' => ['4', '9'],
],
```

These empty entries are intentional and keep the canonical filter shape stable.
