# Module Translation Files

Each module can provide translations independently.

## File location

```text
app/<module>/config/translations/en_GB.php
modules/<vendor-module>/config/translations/en_GB.php
```

## File format

```php
<?php

declare(strict_types=1);

return [
    'admin.users' => 'Admin Users',
    'core.save' => 'Save',
];
```

## Loader

`ModuleTranslationLoader` scans enabled modules and merges their translation arrays. Later modules override earlier values by key.

## Next step

Wire `Translator` into `AdminLayout`, controllers and frontend rendering so labels are pulled from translation keys instead of hardcoded strings.
```
