# Audit table schema ownership

`admin_login_history` and `admin_activity_log` are owned by
`app/zoosper-admin/config/db_schema.php`.

The historical `202607090006_create_audit_login_history.php` file duplicated the
same table and index declarations. Keeping both definitions created a drift risk:
a column or index could be changed in one source while fresh and upgraded
databases followed different definitions.

The migration file is retired. Existing databases keep their recorded migration
history and current tables. Fresh databases create both audit tables in the
module declarative-schema pass. The full SQLite migration test continues to
prove that both tables exist after `Migrator::migrate()`.

This change does not add foreign-key support to the declarative engine. Foreign
keys require a separate driver-aware design covering creation order, alteration,
delete policies, SQLite parity and future module uninstall behaviour.
