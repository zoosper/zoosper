# zoosper/scoped-config

Shared persisted scoped configuration foundation for Zoosper modules.

## Responsibilities
- Own Default, Website, Store and Site scope types and immutable scope context.
- Resolve inherited database overrides from most-specific to least-specific scope.
- Persist, clear and inspect values in `config_scope_values`.
- Provide one shared service binding for Admin, Mail, Settings and Theme.

## Architecture
This package is independent of `zoosper/core` and `zoosper/settings`. Settings owns catalogue, mutation, validation, audit and presentation workflows; this package owns only the reusable persistence and inheritance foundation.

## Dependencies
- PHP 8.5 or newer.
- PDO.

## Extension points
Modules consume `Zoosper\ScopedConfig\ScopeConfigRepository`, `ScopeContext` and `ScopeType`. The shared repository binding may be replaced with an implementation preserving the same inheritance and persistence semantics.

## Testing
```bash
zcomposer test
php8.5 tools/gate.php
```

## Operational notes
The `config_scope_values` table must preserve one value per scope type, scope key and path. Default scope uses a null key. Writes must not flush unrelated application caches. Validate Composer manifests and compile a fresh module manifest before release.
