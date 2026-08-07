<?php

declare(strict_types=1);

it('renders category navigation, section cards and accessible group accordions', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('aria-label="Settings categories"')->toContain('data-category-tab')->toContain('data-category-panel')->toContain('data-section-id')->toContain('data-settings-group')->toContain('<details class="settings-group"')->toContain('<summary>');
});

it('retains scope, typed fields, save, clear, search and source contracts', function (): void {
    $root=dirname(__DIR__,5);$view=file_get_contents($root.'/app/zoosper-settings/resources/views/admin/settings/index.php');
    expect($view)->toContain('method="get"')->toContain('action="<?= $e($saveUrl) ?>"')->toContain('method="post"')->toContain('formaction="<?= $e($clearUrl) ?>"')->toContain('name="_csrf_token"')->toContain('data-settings-card')->toContain('Search settings, modules and paths')->toContain('Source:')->toContain('$setting->type===\'boolean\'')->toContain('$setting->type===\'multiselect\'');
});
