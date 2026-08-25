<?php

declare(strict_types=1);

it('resolves the authenticated actor in the Admin User lifecycle data expression', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Admin/Controller/UserAdminController.php');

    expect($source)
        ->toContain("'lifecycleHtml' => \$user !== null && \$this->lifecycle !== null")
        ->toContain("\$this->lifecycle->actionsHtml(")
        ->toContain("\$this->guard->user() ?? throw new RuntimeException('Authenticated Admin User required while rendering the Admin User form.')")
        ->toContain(": '',")
        ->not->toContain("\$this->lifecycle?->actionsHtml(\$user,")
        ->not->toContain('actionsHtml($user, $actor)');
});
