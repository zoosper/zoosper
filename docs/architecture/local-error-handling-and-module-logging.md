# Local Error Handling and Module Logging

Phase 0.21 wires local logging into the application bootstrap.

## Files

```text
config/logging.php
app/zoosper-core/src/Log/LocalLogger.php
app/zoosper-core/src/Log/LogManager.php
app/zoosper-core/src/Log/ErrorHandler.php
```

## Log files

Default location:

```text
var/log/
```

Default filenames:

```text
system.log
exception.log
admin.log
api.log
auth.log
core.log
page.log
site.log
theme.log
```

## Module-specific services

`ApplicationFactory` registers module loggers into the service container:

```php
$services->set('logger.zoosper-theme', $logManager->module('zoosper-theme'));
```

A future module controller provider can retrieve a logger by string key and pass it into a controller or service that supports logging.

## Safety

`LocalLogger` redacts context keys containing:

```text
password
token
secret
session
```

## Next phase direction

Add typed module logger factories and start injecting module loggers into services that actually need them.
