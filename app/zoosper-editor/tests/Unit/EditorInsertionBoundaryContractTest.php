<?php

declare(strict_types=1);

it('keeps Editor.js insertion wrapper-scoped editor-owned and CI-gated', function (): void {
    $root = dirname(__DIR__, 4);
    $bridge = (string) file_get_contents($root . '/public/assets/admin/js/zoosper-editor-bridge.js');
    $runtime = (string) file_get_contents($root . '/public/assets/admin/js/zoosper-content-editor.js');
    $assets = (string) file_get_contents($root . '/app/zoosper-editor/config/admin_assets.php');
    $package = json_decode((string) file_get_contents($root . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    expect($bridge)->toContain('const instances = new WeakMap()')->toContain("const allowedTypes = new Set(['image'])")->toContain("url.startsWith('/media/')")->toContain('await editor.isReady')->toContain('editor.blocks.insert')->toContain('await synchronise(wrapper, editor)')
        ->and($runtime)->toContain('ZoosperEditorBridge.register(wrapper, editor)')
        ->and($assets)->toContain("'zoosper-admin-editor-bridge'")
        ->and($package['scripts']['test:editor-insertion'] ?? null)->toBe('node --test app/zoosper-editor/tests/Browser/editor-insertion-bridge.test.js')
        ->and($workflow)->toContain('Run Editor insertion behaviour suite');
});
