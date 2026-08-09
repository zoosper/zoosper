# Command line

Run `php bin/zoosper list` for the current command inventory.

Core commands include migration, module compilation, cache clearing, manifest status/checking, deployment, module/command scaffolding, version and release checks. Modules also contribute commands such as initial Admin, Site and Page creation.

Recovery commands including help, list, compile and cache clear remain available without an active database connection. Database-backed commands resolve PDO only when selected.
