# Phase 1.09.1 - Heredoc Detection Hotfix

Phase 1.09 did not apply because the apply tool searched only for a narrow heredoc assignment shape. `UserAdminController.php` does use heredoc, but its opener did not match that narrow regex.

This hotfix scans all heredoc/nowdoc openers, finds the block that contains the admin user form and email field, then inserts:

```php
$localeFieldHtml = $this->renderAdminLocaleField($submitted['locale'] ?? $user->locale ?? null);
```

before the block and inserts only:

```text
{$localeFieldHtml}
```

inside the block.
