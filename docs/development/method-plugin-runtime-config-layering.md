# Phase 1.43a-c: Method Plugin Runtime Config Layering

## Goal

Discover method plugin runtime configuration through the existing config-layering foundation while keeping production runtime interception disabled by default.

## Proposed config shape

```php
return [
    'method_plugins' => [
        'enabled' => false,
        'report_only' => true,
        'allow_list' => [],
    ],
];
```

## Safety model

- Runtime defaults remain disabled.
- Disabled runtime resolves to an empty allow-list, even if module defaults proposed entries.
- Root/project override can disable runtime and clear effective allow-list behaviour.
- No selected service method is invoked during config discovery.
- Plugin output does not replace baseline output.

## Verification

```bash
php8.5 tools/prove-method-plugin-runtime-config-layering.php
php8.5 tools/audit-method-plugin-runtime-config-layering.php
php8.5 vendor/bin/pest app/zoosper-core/tests/Unit/Plugin/MethodPluginRuntimeConfigLayeringTest.php
php8.5 vendor/bin/pest
```

## Next phase

Phase 1.43d-f should inspect `Zoosper\Page\Service\PageRenderer::render` signature and refine fixture arguments without invoking production services.
