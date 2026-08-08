<?php

declare(strict_types=1);

it('uses one section form with non-nested clear controls', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('action="<?= $e($saveUrl) ?>"')
        ->toContain('formaction="<?= $e($clearUrl) ?>"')
        ->toContain('formmethod="post"')
        ->toContain('name="path"')
        ->not->toContain('<form method="post" action="/admin/settings/clear"');
});

it('renders every Phase 9D1 editable type', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');

    expect($view)->toContain('$setting->type===\'boolean\'')
        ->toContain('$setting->type===\'textarea\'')
        ->toContain('$setting->type===\'select\'')
        ->toContain('$setting->type===\'multiselect\'')
        ->toContain("'integer'=>'number'")
        ->toContain("'decimal'=>'number'")
        ->toContain("'email'=>'email'")
        ->toContain("'url'=>'url'");
});

it('keeps the boolean fallback before its checkbox value', function (): void {
    $root = dirname(__DIR__, 5);
    $view = file_get_contents($root . '/app/zoosper-settings/resources/views/admin/settings/index.php');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css');
    $view .= (string) file_get_contents($root . '/app/zoosper-settings/resources/assets/js/settings-workspace.js');
    $hidden = strpos($view, 'type="hidden" name="<?= $e($inputName) ?>" value="0"');
    $checkbox = strpos($view, 'type="checkbox" name="<?= $e($inputName) ?>" value="1"');

    expect($hidden)->not->toBeFalse()
        ->and($checkbox)->not->toBeFalse()
        ->and($hidden)->toBeLessThan($checkbox);
});
