# Phase 1.09 - Safe UserAdminController Locale UI Integration

This phase integrates the admin-user locale field using the confirmed heredoc rendering pattern.

## Integration pattern

```php
$localeFieldHtml = $this->renderAdminLocaleField($submitted['locale'] ?? $user->locale ?? null);
```

The heredoc form then contains only:

```text
{$localeFieldHtml}
```

No raw `<?= ... ?>` tags are inserted into controller source.
