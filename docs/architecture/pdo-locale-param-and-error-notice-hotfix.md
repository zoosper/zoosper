# Phase 1.17.2 - PDO Locale Parameter and Error Notice Hotfix

The rendered admin output showed:

```text
SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
```

This is consistent with SQL containing a `:locale` token while one of the execute arrays does not bind `locale`.

This hotfix verifies every `->execute([...])` block whose nearby prepared SQL contains `:locale` also binds `'locale' => $locale`.

It also restores `.notice-error` styling so error notices are visually obvious like success notices.
