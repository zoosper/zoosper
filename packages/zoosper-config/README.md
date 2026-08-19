# zoosper/config

Zoosper-owned compatibility bridge for shared Marko configuration consumers.

## Responsibilities
- Own the application-wide Marko configuration adapter used by HTTP and console composition.
- Publish the Marko configuration service consumed by the Session module without making Session depend on Core implementation classes.
- Keep Core ownership of module aggregation, project overrides, the application repository, and persisted scoped configuration.
- Keep package-local adapters such as Cache independent where they intentionally have no Core dependency.

## Architecture
The bridge accepts any application repository exposing `get()`, then implements Marko's typed configuration contract. It does not own configuration loading, module merging, settings persistence, or scoped inheritance.

## Dependencies
- PHP 8.5 or newer.
- `marko/config` 0.8.5.
- No dependency on `zoosper/core`.

## Extension points
HTTP and console composition may replace the Marko service binding with another compatible implementation. Feature modules should continue consuming Zoosper's application or scoped configuration boundaries rather than importing Marko directly.

## Testing
```bash
zcomposer test
php8.5 tools/gate.php
```

## Operational notes
Configuration loading must remain database-lazy so recovery commands work when the database is unavailable. Preserve module-default-below-project-override precedence. Do not merge application configuration and persisted scope resolution into one implicit service. Validate Composer manifests and compile a fresh module manifest before release.
