# Admin form processors config hotfix

The page module now explicitly declares both extension groups for `page.form`:

```php
'forms' => [
    'page.form' => [/* section providers */],
],
'processors' => [
    'page.form' => [],
],
```

The empty processor list is intentional. It documents the extension point and allows third-party modules to contribute processors later without touching core code.
