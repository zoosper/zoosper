# Schema & database

Zoosper uses one **declarative schema engine**. Modules describe the desired database shape in PHP; the engine computes additive SQL and records audit snapshots.

## Declare tables in a module

`app/<module>/config/db_schema.php` or `modules/.../config/db_schema.php`:

```php
<?php

declare(strict_types=1);

return [
    'tables' => [
        'acme_notes' => [
            'columns' => [
                'id'         => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                'title'      => ['type' => 'string', 'length' => 190, 'nullable' => false],
                'is_active'  => ['type' => 'boolean', 'nullable' => false, 'default' => false],
                'payload'    => ['type' => 'json', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            ],
            'indexes' => [
                'idx_acme_notes_title' => ['columns' => ['title']],
                'uniq_acme_notes_title' => ['columns' => ['title'], 'unique' => true],
            ],
        ],
    ],
];
```

### Column types

`integer`, `int`, `bigint`, `string` (+ `length`), `text`, `datetime`, `boolean`, `json`, with optional `nullable`, `primary`, `auto_increment`, and `default` (including `'CURRENT_TIMESTAMP'`).

### Indexes

`columns` list plus optional `unique => true`.

## How merging works

`SchemaLoader` merges **same-named tables** from all enabled modules. A third-party module can add columns to a core table by declaring the table name with only the new columns:

```php
'tables' => [
    'pages' => [
        'columns' => [
            'meta_title' => ['type' => 'string', 'length' => 255, 'nullable' => true],
        ],
    ],
],
```

## Safety model

The engine is **additive only**:

- create missing tables
- add missing columns
- add missing indexes

It does **not** drop or alter columns. Destructive changes belong in explicit, reviewed migration files.

## Commands

```bash
php bin/zoosper-schema validate    # validate all module schemas
php bin/zoosper-schema diff        # SQL that would run
php bin/zoosper-schema apply       # apply + snapshot
php bin/zoosper-schema snapshots   # audit history
php bin/zoosper migrate            # file migrations + schema workflow
```

Each `apply` writes to `schema_snapshots` (hash, statements JSON, timestamp).

## Drivers

SQL is generated for the active PDO driver (MySQL production policy; SQLite allowed for local dev when configured).

## PCI note

Schema describes structure only. Never put secrets in column defaults. Store hashes/ciphertext for sensitive values (2FA secrets, recovery code hashes, token hashes).

## Related guides

- [Entity save lifecycle](entity-save-lifecycle.md) — extension fields vs core columns
- [Modularity & modules](modularity-and-modules.md)
