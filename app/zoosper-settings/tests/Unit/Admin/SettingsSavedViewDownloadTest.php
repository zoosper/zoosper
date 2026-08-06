<?php

declare(strict_types=1);

it('downloads a versioned value-free JSON file and revokes its object URL', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('id="settings-download-views"')
        ->toContain("new Blob([payload],{type:'application/json'})")
        ->toContain("link.download='zoosper-settings-saved-views.json'")
        ->toContain('URL.revokeObjectURL(url)')
        ->toContain("copyStatus.textContent='Downloaded saved views JSON'");
});
