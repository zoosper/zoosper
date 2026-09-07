<?php

declare(strict_types=1);

it('resolves the authenticated actor while composing lifecycle and lockout actions', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Admin/Controller/UserAdminController.php');

    expect($source)
        ->toContain("'lifecycleHtml' => \$user !== null")
        ->toContain("\$this->lifecycle?->actionsHtml(")
        ->toContain("\$this->guard->user() ?? throw new RuntimeException('Authenticated Admin User required while rendering the Admin User form.')")
        ->toContain("\$this->accountUnlock?->actionsHtml(\$user)")
        ->toContain(": '',")
        ->not->toContain("\$this->lifecycle?->actionsHtml(\$user,");
});
