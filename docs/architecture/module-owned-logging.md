# Module-Owned Logging

Phase 0.22 moves module log filenames out of global `config/logging.php`.

## Module config

Each module can provide:

```text
app/<module>/config/logging.php
```

Example:

```php
return [
    'file' => 'theme.log',
];
```

## Service ID

By default, the logger service is registered as:

```text
logger.<module-name>
```

Example:

```text
logger.zoosper-theme
```

The service ID can be customised:

```php
return [
    'service' => 'logger.custom-theme',
    'file' => 'theme.log',
];
```

This avoids hard-coding module log files inside `ApplicationFactory`.
