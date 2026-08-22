# Modules

A Composer package is discoverable as a Zoosper module when it explicitly declares the package type.

```json
{
  "type": "zoosper-module"
}
```

A typical module may contain `module.php`, `config/`, `src/`, `database/migrations/`, `resources/` and `tests/`.

Module identity must be unique across application, package and installed-vendor layers. Cross-layer collisions fail loudly instead of silently masking one copy.

After adding or changing module metadata, rebuild discovery with Composer autoload generation, `cache:clear`, `compile` and `module:manifest:status`.

## Package documentation policy

The root `docs/` directory is the canonical website source. Every first-party Composer module or package must keep exactly one meaningful root `README.md` as its co-located technical reference, covering current responsibilities, architecture, configuration, dependencies, extension points, testing, and operational notes. Patch notes, completed phase records, readiness stubs, and duplicated architecture or operations trees are not permanent documentation.

## Documentation integrity

Public project summaries must be reconciled with detailed phase notes and current source whenever a phase closes. The root README describes the current product surface, `SECURITY.md` describes the current release/security scope, and `ROADMAP.md` owns open-versus-closed delivery status. Package READMEs remain co-located technical references and must not become independent roadmaps.
