<?php

declare(strict_types=1);

it('resolves the authenticated actor while composing lifecycle and lockout actions in both form paths', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Admin/Controller/UserAdminController.php');

    expect(substr_count($source, "'lifecycleHtml' => \$user !== null") + substr_count($source, '$lifecycleHtml = $user !== null'))
        ->toBe(2)
        ->and(substr_count($source, '$this->accountUnlock?->actionsHtml($user)'))->toBe(2)
        ->and(substr_count($source, "\$this->guard->user() ?? throw new RuntimeException('Authenticated Admin User required while rendering the Admin User form.')"))->toBe(2)
        ->and($source)->not->toContain('$this->lifecycle?->actionsHtml($user,');
});
