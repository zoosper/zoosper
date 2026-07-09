# Phase 0.10 - Declarative Schema

Zoosper should add Magento-like declarative schema convenience using PHP config files:

```text
app/<module>/config/db_schema.php
modules/<vendor-module>/config/db_schema.php
```

Initial safe operations:

- create table
- add column
- add index
- add unique index

Destructive operations should require explicit allow-listing.
