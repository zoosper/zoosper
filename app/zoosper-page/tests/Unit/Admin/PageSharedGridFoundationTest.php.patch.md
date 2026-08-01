# Update the shared Page Grid filter contract

Replace:

```php
expect($definition->filterKeys())->toBe(['q', 'status', 'site_id']);
```

with:

```php
expect($definition->filterKeys())->toBe([
    'q',
    'title',
    'slug',
    'status',
    'site_id',
]);
```

Title and Slug are now real bound repository filters so their controls can follow
column visibility independently. Global Search remains available separately.
