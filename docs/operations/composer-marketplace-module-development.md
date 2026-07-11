# Composer marketplace module development

## Local path repository during development

A developer can test a Composer module locally using a path repository in the Zoosper project:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../zoosper-blog"
    }
  ],
  "require": {
    "acme/zoosper-blog": "*"
  }
}
```

Then run:

```bash
composer update acme/zoosper-blog
composer dump-autoload
php tools/diagnose-composer-modules.php
php tools/diagnose-modules.php
```

## Module config files

Marketplace modules can provide:

```text
config/services.php
config/controllers.php
config/admin_routes.php
config/admin_menu.php
config/db_schema.php
config/admin_assets.php
config/logging.php
resources/views/
src/
```

## Security considerations

Only install trusted Composer packages. Module config files are PHP and execute during application bootstrap. Do not store OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data or customer-private values in module metadata.
