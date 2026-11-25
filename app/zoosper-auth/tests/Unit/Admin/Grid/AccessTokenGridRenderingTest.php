<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AccessToken\AccessTokenGrid;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminUrlGenerator;

it('renders token grid values safely through the grid column callbacks', function (): void {
    $grid = new AccessTokenGrid(new PDO('sqlite::memory:'), 1, new AdminUrlGenerator(ConfigRepository::fromArray([])), 'csrf<token>');
    $columns = [];
    foreach ($grid->definition()->columns as $column) {
        $columns[$column->key] = $column;
    }

    expect($columns['name']->renderValue(' <name> ', []))->toContain('&lt;name&gt;')
        ->and($columns['scopes']->renderValue('read, <write>', []))->toContain('&lt;write&gt;')
        ->and($columns['scopes']->renderValue('', []))->toContain('None')
        ->and($columns['expires_at']->renderValue('', []))->toContain('Never')
        ->and($columns['last_used_at']->renderValue('2026-01-02 12:00:00', []))->toContain('2026-01-02T12:00:00')
        ->and($columns['status']->renderValue('<unknown>', []))->toContain('status--revoked')->toContain('&lt;unknown&gt;')
        ->and($columns['actions']->renderValue(null, ['id' => 7, 'revoked_at' => null]))->toContain('/admin/access-tokens/7/revoke')->toContain('csrf&lt;token&gt;')
        ->and($columns['actions']->renderValue(null, ['id' => 7, 'revoked_at' => '2026-01-01']))->not->toContain('<form');
});