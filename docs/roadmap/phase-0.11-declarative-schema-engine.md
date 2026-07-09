# Phase 0.11 - Declarative Schema Engine

After ACL tree/user assignment, the next best platform step is a Magento-inspired declarative schema engine.

## Proposed files

```text
app/<module>/config/db_schema.php
modules/<vendor-module>/config/db_schema.php
```

## MVP operations

- create table
- add column
- add index
- add unique index

## Later safeguards

- destructive operation allow-list
- rename column mapping
- schema diff command
- schema apply command
- schema dump command
