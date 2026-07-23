# Admin Form Config Layering Migration Plan

Phase 1.40p-t prepares a source-specific migration plan for admin form/UI config loading.

## Why this path

The config source inventory found admin UI/form-style config such as:

```text
app/zoosper-page/config/admin_forms.php
app/zoosper-page/config/admin_ui.php
app/zoosper-auth/config/admin_ui.php
```

The previous read-only pilot proved that these files can be loaded and merged through the new file-layer adapter without changing runtime code.

## Target source area

Likely source files include:

```text
app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php
app/zoosper-admin/src/Form/AdminFormConfigAggregator.php
app/zoosper-core/src/Config/ConfigFileLayeredLoader.php
app/zoosper-core/src/Config/LayeredConfigLoader.php
```

## Migration safety rules

The first runtime migration should:

- avoid routes, middleware, auth, CSRF, and service config;
- avoid schema config because it touches install/update paths;
- preserve existing admin form extension behaviour;
- provide source snapshots before patching;
- provide read-only planner output before mutating any runtime class;
- be backed by Pest tests and audit tooling.

## This phase

This phase only discovers and plans. It does not patch runtime loader classes yet.

## Next phase

If the planner reports a safe source shape, the next phase can add a guarded patch tool that migrates exactly one admin form/UI loader to `ConfigFileLayeredLoader` while preserving current behaviour.
