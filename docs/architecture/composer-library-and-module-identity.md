# Composer library and runtime module identity

First-party Composer packages may be either runtime modules or supporting
libraries.

A package with `type: zoosper-module` must declare:

```json
"extra": { "marko": { "module": true } }
```

A package with `type: library` must not claim runtime module identity. Libraries
are installed and autoloaded by Composer but are not discovered by
`ModuleRegistry`, do not appear in `var/cache/modules.php`, and do not affect the
runtime module count.

All first-party packages, regardless of type, must remain free of retired
`extra.zoosper` metadata. Architecture tests classify manifests by Composer
`type` before enforcing module-only requirements.
