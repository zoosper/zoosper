<?php
declare(strict_types=1);
it('keeps the browser picker Media-owned wrapper-scoped accessible and CI-gated', function (): void {
    $root = dirname(__DIR__, 5);
    $runtime = (string) file_get_contents($root . '/packages/zoosper-media/resources/admin/js/editor-media-picker.js');
    $assets = (string) file_get_contents($root . '/packages/zoosper-media/config/admin_assets.php');
    expect($runtime)->toContain("endsWith('/upload')")->toContain('showModal()')->toContain("ZoosperEditorBridge.insert(wrapper, 'image'")->toContain("credentials: 'same-origin'")->toContain("dialog.addEventListener('cancel'")
        ->and($assets)->toContain("'screens' => ['pages']")->toContain('editor-media-picker.js')->toContain('editor-media-picker.css');
});
