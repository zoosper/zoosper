# SmtpConfig class not found after Phase 0.34

## Symptom

```text
Class "Zoosper\Mail\Config\SmtpConfig" not found
```

## Cause

Phase 0.34 wires the mail service into `ApplicationFactory`, but it depends on the Phase 0.33 mail module files. If the Phase 0.33 files were not copied, or Composer autoload was not regenerated, the bootstrap cannot find `Zoosper\Mail\Config\SmtpConfig`.

## Fix

Apply this hotfix package, then run:

```bash
composer dump-autoload
php -l app/zoosper-mail/src/Config/SmtpConfig.php
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Zoosper\\Mail\\Config\\SmtpConfig'));"
```

Expected:

```text
bool(true)
```

## PCI-aware note

Do not put real SMTP passwords in source code. Use environment variables or secret storage.
