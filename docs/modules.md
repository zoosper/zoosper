# Modules

A Composer package is discoverable as a Zoosper module when it explicitly declares the package type:

```json
{
  "type": "zoosper-module"
}
```

A typical module contains `module.php`, `config/`, `src/`, `database/migrations/`, `resources/` and `tests/`.

## Module layers and directory structure

Zoosper organizes codebase extensions across three deliberate layers:

1. **`app/*` (Internal Path-Repository Modules)**
   Core monorepo-internal modules that form the platform baseline (`zoosper-core`, `zoosper-auth`, `zoosper-admin`, `zoosper-page`, `zoosper-site`, `zoosper-theme`, `zoosper-mail`, `zoosper-two-factor`, `zoosper-settings`, `zoosper-global-announcements`, etc.). These modules share the monorepo root repository and are registered via Composer path repositories.

2. **`packages/*` (Standalone Composer Packages)**
   Independently exportable, publishable packages (`zoosper-errors`, `zoosper-media`, `zoosper-grid`, `zoosper-admin-grid`, `zoosper-pagination`, `zoosper-logger`, `zoosper-cache`, `zoosper-session`, `zoosper-config`, etc.). Each standalone package maintains minimal coupling, its own `composer.json`, and a `.gitattributes` file marking tests and dev-tooling as `export-ignore`.

3. **`modules/*` (Pluggable Drop-In Extensions)**
   Local extension workspace for site-specific plugins or unmanaged third-party drop-ins. `ModuleRegistry` scans both `modules/*/module.php` and `modules/*/*/module.php` dynamically, enabling rapid prototyping or deployment of extensions without registering standalone Composer repositories.

Module identity must be unique across application, package, modules and installed-vendor layers. Cross-layer collisions fail loudly instead of silently masking one copy.

After adding or changing module metadata, rebuild discovery with Composer autoload generation, `cache:clear`, `compile` and `module:manifest:status`.

## Package documentation policy

The root `docs/` directory is the canonical website source. Every first-party Composer module or package must keep exactly one meaningful root `README.md` as its co-located technical reference, covering current responsibilities, architecture, configuration, dependencies, extension points, testing, and operational notes. Patch notes, completed phase records, readiness stubs, and duplicated architecture or operations trees are not permanent documentation.

## Documentation integrity

Public project summaries must be reconciled with detailed phase notes and current source whenever a phase closes. The root README describes the current product surface, `SECURITY.md` describes the current release/security scope, and `ROADMAP.md` owns open-versus-closed delivery status. Package READMEs remain co-located technical references and must not become independent roadmaps.
