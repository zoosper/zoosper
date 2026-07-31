# Migrations ignore the compiled module cache

Database migration correctness must not depend on the runtime module-manifest
cache. A deploy can install a new module while `var/cache/modules.php` still
represents the previous release.

Both traditional module migration discovery and declarative schema loading use
`ModuleRegistry::discoverModulesLive()`. Therefore `bin/zoosper migrate` sees
the current installed module graph even when ordinary runtime discovery can
still read an older valid cache.

A regression test creates a stale empty compiled manifest, installs a new module
with a migration, proves normal cached discovery is empty, then proves the
migrator still executes and records the new module migration.
