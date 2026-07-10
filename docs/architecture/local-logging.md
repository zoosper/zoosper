# Local Logging

Phase 0.20 introduces a lightweight local logging foundation.

## Configuration

```text
config/logging.php
```

Default values:

```php
return [
    'path' => 'var/log',
    'default_file' => 'system.log',
    'modules' => [
        'zoosper-theme' => 'theme.log',
    ],
];
```

## Usage

```php
$logger = $logManager->module('zoosper-theme');
$logger->error('Template failed', ['template' => $template]);
```

## Why

This follows the Magento-style debugging habit of using dedicated log files per module to reduce noise when troubleshooting one particular feature.

## Next step

Wire `LogManager` into `ApplicationFactory`, error handling and selected module services/controllers.
