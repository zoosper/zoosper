# Apply Zoosper Phase 0.21 Local Error Handling and Module Logging

Apply from repository root:

```bash
unzip zoosper-phase-0.21-local-error-handling-module-logging-update.zip -d /tmp/zoosper-phase-0.21
cp -R /tmp/zoosper-phase-0.21/zoosper-phase-0.21-local-error-handling-module-logging-update/* .
composer dump-autoload
php bin/zoosper migrate
```

Smoke test:

```bash
php -l config/app.php
php -l config/logging.php
php -l app/zoosper-core/src/Log/LocalLogger.php
php -l app/zoosper-core/src/Log/LogManager.php
php -l app/zoosper-core/src/Log/ErrorHandler.php
php -l app/zoosper-core/src/Bootstrap/ApplicationFactory.php
```

Autoload test:

```bash
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Core\\Log\\LogManager'));"
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Core\\Log\\ErrorHandler'));"
```

Local log test:

```bash
php -r "require 'vendor/autoload.php'; $c=Zoosper\\Core\\Config\\ConfigRepository::fromPath(__DIR__.'/config'); $m=new Zoosper\\Core\\Log\\LogManager($c,__DIR__); $m->module('zoosper-theme')->info('theme logger smoke test');"
ls -la var/log
cat var/log/theme.log
```

Browser test:

```text
/admin/
/admin/themes
/home
```

Expected log files are written under:

```text
var/log/
```
