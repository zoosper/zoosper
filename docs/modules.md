# Modules

A Composer package is discoverable as a Zoosper module when it explicitly declares both the package type and Marko module flag.

```json
{
  "type": "zoosper-module",
  "extra": {
    "marko": {
      "module": true
    }
  }
}
```

A typical module may contain `module.php`, `config/`, `src/`, `database/migrations/`, `resources/` and `tests/`.

Module identity must be unique across application, package and installed-vendor layers. Cross-layer collisions fail loudly instead of silently masking one copy.

After adding or changing module metadata, rebuild discovery with Composer autoload generation, `cache:clear`, `compile` and `module:manifest:status`.
