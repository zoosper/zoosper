# Phase 0.70 - Project structure hygiene

Zoosper's root folders are intentionally separated by responsibility:

```text
app/       first-party Zoosper modules
modules/   local/custom/community modules
themes/    theme source templates/assets
assets/    source assets for build pipelines
public/    webroot only
storage/   private/original uploaded files
var/       runtime/cache/log/quarantine/generated files
config/    application config
database/  migrations/schema/seeders
deploy/    infrastructure snippets
docs/      documentation sources
tools/     developer scripts, gradually migrated to bin/zoosper
bin/       stable CLI entrypoints
tests/     automated tests
```

`public/` must not contain runtime/private/source folders such as `var`, `storage`, `vendor`, `node_modules`, `app`, `config`, `modules`, `themes` or `tools`.
