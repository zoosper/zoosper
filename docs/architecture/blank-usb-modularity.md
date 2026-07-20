# Blank USB modularity principle

Zoosper core should behave like a blank USB: useful, stable and empty by default.

```text
core + movie module    = movie CMS
core + health module   = health CMS
core + software module = software CMS
```

That means the core should provide the runtime substrate only:

```text
- module discovery
- configuration loading
- dependency injection
- routing and middleware
- declarative schema
- events and future interceptors
- CLI scaffolding
- diagnostics
```

Business capabilities should live in removable packages. Adding or removing a package should add or remove that capability without breaking the core runtime.

Phase 1.37p supports this principle by adding package-aware module scaffolding under `packages/`.
