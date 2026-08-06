<?php

declare(strict_types=1);

it('resets all personal Settings workspace metadata without touching configuration values', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('id="settings-reset-personal-workspace"')
        ->toContain("window.confirm('Reset all personal Settings workspace preferences?')")
        ->toContain("'zoosper.settings.sourceFilter','zoosper.settings.moduleFilter','zoosper.settings.density'")
        ->toContain("history.replaceState(null,'',location.pathname)")
        ->toContain("copyStatus.textContent='Reset personal Settings workspace'")
        ->not->toContain('clearConfigurationValues');
});
