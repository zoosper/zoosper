# Declarative longtext support

Zoosper's declarative schema supports `longtext` for content that may exceed
MySQL's ordinary `TEXT` capacity.

Driver mapping:

```text
MySQL/MariaDB  longtext -> LONGTEXT
SQLite         longtext -> TEXT
```

The SMTP email log body columns use this type. Existing MySQL/MariaDB tables are
upgraded by a module-owned migration. The migration checks whether the table
exists, so fresh installations rely on the declarative definition and do not
fail before schema creation. SQLite requires no alteration because its TEXT
storage class is not limited to MySQL TEXT capacity.

`text` remains appropriate for bounded diagnostic fields such as error messages.
