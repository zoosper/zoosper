# Admin UI/Form Config Layering Pilot

Phase 1.40k-o introduces a read-only pilot for layered admin UI/form-style config.

## Why this target

The config source discovery and first-migration planner found many config sources. The highest scoring candidates included several schema files, but schema config affects install/update paths and should not be the first runtime migration target.

Admin UI/form-style config is a better first pilot because it is closer to presentation semantics and can be smoke-tested without mutating request dispatch, authentication, middleware ordering, services, or schema execution.

## Scope

This phase is still read-only. It does not replace existing runtime loaders.

It adds a reusable file-layer adapter around the existing `LayeredConfigLoader` and a smoke command that merges discovered admin UI/form-style config files into a report.

## Candidate files

The smoke command looks for known files such as:

```text
app/zoosper-page/config/admin_forms.php
app/zoosper-page/config/admin_ui.php
app/zoosper-auth/config/admin_ui.php
```

Only files that exist and return arrays are included.

## Safety rules

This phase deliberately avoids:

```text
routes
admin_middleware
middleware
services
auth/csrf-sensitive config
db_schema runtime migration
```

## Next phase

If this pilot is green, the next phase can migrate one specific admin UI/form loader to `ConfigFileLayeredLoader` behind tests and an audit, while keeping route/middleware/service/schema loaders unchanged.
