# Verifier string interpolation hotfix

Verifier tools that scan PHP source must avoid PHP variable interpolation in expected source snippets.

Use single-quoted strings or escaped dollar signs when checking for source patterns such as:

```php
$this->t('Message')
'action' => $action
```

This keeps verifiers stable and avoids false failures unrelated to runtime code.
