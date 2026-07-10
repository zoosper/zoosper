# Zoosper Mail namespace autoload hotfix

## Symptom

```text
Class "Zoosper\Mail\Config\SmtpConfig" not found
```

but this passes:

```bash
php -l app/zoosper-mail/src/Config/SmtpConfig.php
```

and this returns false:

```bash
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Mail\\Config\\SmtpConfig'));"
```

## Cause

The PHP file exists, but Composer does not know how to autoload the new namespace:

```text
Zoosper\Mail\
```

## Fix

Add this PSR-4 mapping to `composer.json`:

```json
"Zoosper\\Mail\\": "app/zoosper-mail/src/"
```

Then regenerate Composer autoload files:

```bash
composer dump-autoload
```

## PCI-aware note

This fix only changes autoload config. It does not add real SMTP credentials. SMTP passwords must stay in `.env` or secret storage and must never be committed or logged.
