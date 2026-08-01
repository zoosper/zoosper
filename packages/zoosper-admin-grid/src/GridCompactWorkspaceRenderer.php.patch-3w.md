# Filter-to-column linkage

Render `data-grid-filter-columns` on each filter label:

```php
$dependencies = match ($filter->key) {
    'q' => '',             // global search always remains available
    'title' => 'title',
    'slug' => 'slug',
    'status' => 'status',
    'site_id' => 'site_name',
    default => $filter->key,
};

$html .= '<label data-grid-filter-columns="' . $this->e($dependencies) . '">';
```

Thus hiding Slug removes both the Slug table column and the Slug filter. The same
rule applies to Title, Status and Site. Global Search is independent and remains
available.
