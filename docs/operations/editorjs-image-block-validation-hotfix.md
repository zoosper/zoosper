# Editor.js image block validation hotfix operations

Phase 1.37m.5 fixes a parse error introduced by the image validation hotfix where the message `Editor's block JSON...` used an unescaped apostrophe inside a single-quoted PHP string.

Run:

```bash
php8.5 -l app/zoosper-page/src/Content/BlockJsonValidator.php
vendor/bin/pest app/zoosper-page/tests/Unit/Content/BlockJsonValidatorImageTest.php
PHP=php8.5 composer dump-autoload
PHP=php8.5 bin/verify
```

Then retry saving the page with the uploaded image block.
