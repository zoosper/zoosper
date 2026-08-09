# Modules

A Composer package is discoverable as a Zoosper module when it explicitly declares both the package type.

```json
{
  "type": "zoosper-module"
}
```

A typical module may contain `module.php`, `config/`, `src/`, `database/migrations/`, `resources/` and `tests/`.

Module identity must be unique across application, package and installed-vendor layers. Cross-layer collisions fail loudly instead of silently masking one copy.

After adding or changing module metadata, rebuild discovery with Composer autoload generation, `cache:clear`, `compile` and `module:manifest:status`.

## Package documentation policy

The root `docs/` directory is the canonical website source. Module and package directories may keep one concise `README.md` only when the component has independently useful ownership or integration guidance. Patch notes, completed phase records, readiness stubs and duplicated architecture or operations trees are not permanent documentation.
