# ADR: Marko CLI Ownership and Lazy Dependencies

## Status

Accepted direction. Implementation follows configuration consolidation.

## Context

`bin/zoosper` currently performs application bootstrap, configuration loading,
module discovery, container creation, logging setup, error handling, database
connection and command dispatch. Module command loading receives an already
constructed PDO connection.

This makes recovery commands depend on infrastructure they do not use and
causes the CLI to duplicate HTTP bootstrap responsibilities.

## Decision

Marko owns generic command discovery and dispatch after the real `marko/cli`
package is added and its locked API is verified. Zoosper modules own CMS
commands and command-specific dependencies.

Database and other expensive services resolve lazily through the command's
actual dependency graph. Console application construction, help, listing,
cache clearing and filesystem-only compilation must not require PDO.

`bin/zoosper` becomes a thin executable entry point. It must not assemble an
independent application container or configuration path.

## Command capability model

Command requirements must be explicit through injected dependencies or Marko
command metadata. A central command-name allow-list for database-free commands
is not an acceptable permanent design.

## Required tests for the implementation phase

- Console construction does not request a database connection.
- Help and command listing succeed with a deliberately failing connection factory.
- Database-free commands do not request PDO.
- Database-required commands request PDO only when executed.
- Database failures are formatted, redacted and return a non-zero exit code.
- Module-contributed commands use the same configuration and module graph as HTTP.

## Consequences

- Recovery tooling remains available during database outages.
- The console stops acting as a second application kernel.
- Existing Zoosper command classes may remain if their contract adapts cleanly;
  generic discovery and dispatch should not remain duplicated.
