# Distribution, compatibility and support

This document defines the supported distribution, extension, compatibility, upgrade and support contract for the current Zoosper release line. It does not claim that a stable release has shipped. The latest immutable release and active development status remain defined by the current-development page.

## Distribution model

Zoosper supports the root `zoosper/zoosper` project as a synchronised monorepo release. First-party packages under `app/` and `packages/` are installed through the root Composer path repositories and the committed root `composer.lock`. Their explicit candidate versions come from root `options.versions`; compatible first-party dependencies use bounded `^0.3.1@alpha` constraints. Individual first-party package publication is not part of the supported distribution contract for this release line.

Production delivery supports either a clean tagged root checkout with locked production dependencies or the deterministic production artifact built by `php8.5 tools/build-production-artifact.php`. The artifact materialises first-party packages and must pass isolated extraction, prohibited-path, manifest and checksum verification.

### Third-party and site-specific modules

A Composer-delivered extension must declare type `zoosper-module`, provide its own PSR-4 autoloading and package metadata, and be required by the root project. Add its Composer repository only when it is not available from an already configured repository, require an explicit reviewed version constraint, update and review the root lock file, then compile and verify the module manifest.

A site-specific local extension may live under `modules/<vendor>/<module>` or `modules/<module>` and must provide `module.php`. Local drop-ins are deployment-owned, are not included automatically in an upstream Zoosper release artifact, and must be included in that deployment's tests, security review, upgrade rehearsal and rollback plan.

Never replace reviewed package constraints with a floating development branch for a production deployment. Module identities must remain unique across application, package, local-module and installed-vendor layers.

## Compatibility policy

Until a stable release is explicitly selected and tagged, Zoosper remains public alpha and compatibility can change between immutable prereleases. Every breaking change still requires an explicit changelog entry, migration guidance where applicable, and a new immutable release. Published tags and artifacts are never retargeted.

For a future stable line, the following are public compatibility boundaries:

- documented PHP interfaces, value objects and service identifiers intended for module use;
- module discovery metadata and documented configuration contribution files;
- documented CLI command names, arguments, exit semantics and machine-readable output;
- documented API routes, field maps, authentication scopes, failure categories and pagination semantics;
- configuration keys and their security-sensitive defaults;
- database migration history and persisted schema/data contracts;
- template, asset and extension contribution points explicitly documented for third-party use.

Patch releases may fix defects and security issues without changing documented behaviour. Minor releases may add optional interfaces, fields, configuration, commands and schema in a backward-compatible manner. Removing or renaming a public boundary, adding a required argument or configuration key, changing a stored-data meaning, weakening a security default, or changing API/CLI semantics requires a documented migration path and a new incompatible release line. Internal classes and undocumented implementation details are not compatibility promises.

Deprecations must identify the replacement and remain functional for the remainder of the supported release line. A deprecated public boundary is removed only in an incompatible release with upgrade documentation.

## Upgrade and support policy

The supported deployment unit is the root project with its committed lock file. PHP 8.5 or later is required. SQLite 3.35 or later is supported for local development and automated proof; MySQL 8.0 or MariaDB 10.6 or later is the staging and production target. Required extensions and platform checks remain defined by the root Composer metadata and release checklist.

Before an upgrade, retain restorable code, database and Media rollback points. Review the changelog and migration notes, rehearse from every release line claimed as supported, run migrations against a disposable database, run them again to prove idempotency, reconcile foreign keys, compile the module manifest and pass release checks. Do not assume migrations are automatically reversible and do not blindly retry failed MySQL DDL.

For the current public-alpha line, support is limited to the latest immutable prerelease and current `dev` where a fix has not yet been released. Security reports use the private process in `SECURITY.md`. Once a stable line is selected, its exact security-fix lifetime and supported upgrade origins must be recorded before tagging; no unstated long-term-support promise is made.

A release may claim an upgrade path only when the repository-owned SQLite and target-MySQL preservation proofs pass from the named immutable origin. Skipping releases is supported only when that exact origin-to-target path has passing evidence. Rollback after a schema-changing deployment uses the pre-deployment code, database and Media backup rather than an assumed down migration.
