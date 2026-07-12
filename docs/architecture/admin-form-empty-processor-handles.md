# Admin form empty processor handles

Empty processor handles are valid extension declarations.

Example:

```php
'processors' => [
    'page.form' => [],
],
```

This means the page form has a processor extension point even when core does not provide any built-in processor classes yet. Third-party modules can later add processors for the same handle without modifying core code.
