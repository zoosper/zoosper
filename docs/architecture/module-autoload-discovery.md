# Module Autoload Discovery

Composer PSR-4 does not support a `Zoosper\**` wildcard mapping for Zoosper's current module layout:

```text
app/zoosper-page/src
app/zoosper-media/src
modules/acme-blog/src
```

A broad mapping such as `Zoosper\ => app/` would make Composer look for classes under paths that do not match the module-folder convention.

Phase 1.37b adds `ModuleAutoloadSynchronizer`, which scans enabled module `module.php` files and keeps Composer's explicit PSR-4 mappings in sync.

## Convention

A module named:

```php
'name' => 'Zoosper_Media'
```

maps to:

```json
"Zoosper\\Media\\": "app/zoosper-media/src/"
```

If the module has tests, it also maps:

```json
"Zoosper\\Media\\Tests\\": "app/zoosper-media/tests/"
```

This keeps the drop-in module model while preserving modern Composer autoloading.
