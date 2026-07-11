# HTML sanitiser setup

Install the recommended HTML Purifier dependency:

```bash
composer require ezyang/htmlpurifier:^4.19
composer dump-autoload
```

Or use the helper:

```bash
php tools/add-html-purifier-dependency.php
composer update ezyang/htmlpurifier
composer dump-autoload
```

Verify:

```bash
php tools/verify-html-sanitizer.php
php tools/demo-html-sanitizer.php
```

Local fallback:

```env
HTML_SANITIZER_DRIVER=basic
```

Use `basic` only for local fallback testing. Production WYSIWYG content should use `htmlpurifier`.
