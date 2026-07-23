# Config Layering Foundation

Phase 1.40a-e starts the config-layering roadmap arc.

## Goal

Zoosper modules already own many types of configuration. The next modularity improvement is to make config precedence explicit and reusable so modules can ship defaults while the root project can override them safely.

## Desired precedence

The intended order is lowest to highest priority:

```text
1. core package defaults
2. module package defaults
3. app/root config
4. environment/local config
5. runtime/in-memory overrides
```

Higher layers override lower layers.

## Scope of this phase

This phase only adds the merge primitive and documentation. It does not replace any existing config loader yet.

## New classes

```text
Zoosper\Core\Config\LayeredConfigResult
Zoosper\Core\Config\LayeredConfigLoader
```

## Merge behaviour

- Associative arrays are merged recursively.
- Scalar values in higher layers replace lower-layer values.
- List arrays are replaced by higher layers instead of being appended, avoiding surprising route/middleware duplication.
- Source names are preserved for diagnostics.

## Next phases

After this foundation is green, the next phase should audit current config loading points and then migrate one low-risk config type to the layered loader.
