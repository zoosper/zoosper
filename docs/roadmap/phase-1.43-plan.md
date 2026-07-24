# Phase 1.43 Plan — Config-Layered Method Plugin Runtime Configuration Discovery

## Objective

Connect the method plugin runtime controls to the config-layering foundation without enabling production runtime plugin execution.

The purpose of Phase 1.43 is discovery and proof only. It should not intercept real services and should not add runtime allow-list entries to production defaults.

## Why this phase is next

Phase 1.41 created the method plugin/interceptor foundation.
Phase 1.42 discovered and planned a selected report-only candidate.
The next safe step is to prove runtime plugin configuration can be discovered and layered correctly.

This aligns with reviewer guidance to continue safety-first modularity and to improve config/bootstrap drift protection.

## Proposed sub-phases

### Phase 1.43a — runtime config shape discovery

Add a documented method plugin runtime config shape, for example:

```php
return [
    'method_plugins' => [
        'enabled' => false,
        'report_only' => true,
        'allow_list' => [],
    ],
];
```

This is only a proposed shape. Implementation should follow existing config-layering conventions in the repo.

Acceptance criteria:

- Config shape is documented.
- Defaults resolve to disabled.
- Empty allow-list is the default.
- No runtime service is invoked.

### Phase 1.43b — layered runtime config proof

Use the existing config-layering foundation to prove module defaults and root/project overrides behave correctly.

Acceptance criteria:

- Module default can define a report-only allow-list candidate.
- Root/project override can disable runtime config.
- Root/project override can clear allow-list entries.
- Final merged output remains disabled unless explicitly overridden in a controlled test fixture.

### Phase 1.43c — audit, tests, and documentation

Add audit tooling and Pest coverage.

Acceptance criteria:

- Audit confirms runtime is disabled by default.
- Audit confirms default allow-list count is 0.
- Audit confirms config discovery does not invoke selected service methods.
- Full Pest suite remains green.

## Hard safety boundaries

- Do not enable production runtime interception.
- Do not invoke `PageRenderer::render` from runtime paths.
- Do not replace baseline output with plugin output.
- Do not add default allow-list entries to production config.
- Do not introduce global interception.

## Deliverables

Expected deliverables:

- Config shape documentation.
- Runtime config discovery/proof tool.
- Root override proof tool.
- Audit tool.
- Pest regression test.
- Roadmap status fragment.

## Suggested commit message

```text
Phase 1.43a-c: discover layered method plugin runtime config
```
