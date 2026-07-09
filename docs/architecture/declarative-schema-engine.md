# Declarative Schema Engine

Phase 0.11 introduces a Magento-inspired declarative schema engine for Zoosper.

## Goal

Developers declare the final desired schema in module-local PHP config files:

```text
app/<module>/config/db_schema.php
modules/<vendor-module>/config/db_schema.php
```

The engine compares declarations against the current database and generates the additive SQL needed to make the database match.

## Supported MVP operations

- create missing table
- add missing column
- add missing index
- add missing unique index

## Intentionally not supported yet

- drop table
- drop column
- alter column type
- rename column
- foreign key reconciliation

Destructive changes should later require explicit allow-listing.

## Commands

```bash
php bin/zoosper-schema diff
php bin/zoosper-schema apply
```

## Example

```php
return [
    'example_table' => [
        'columns' => [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'title' => ['type' => 'string', 'length' => 190],
            'created_at' => ['type' => 'datetime'],
        ],
        'indexes' => [
            'idx_example_title' => ['columns' => ['title']],
        ],
    ],
];
```
