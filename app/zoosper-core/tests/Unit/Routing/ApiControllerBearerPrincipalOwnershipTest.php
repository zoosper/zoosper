<?php

declare(strict_types=1);

it('keeps migrated bearer controllers on middleware-established principal ownership', function (): void {
    $root = dirname(__DIR__, 5);
    foreach ([
        'app/zoosper-api/src/Controller/TokenMeController.php',
        'app/zoosper-auth/src/Api/RoleApiController.php',
        'app/zoosper-theme/src/Api/ThemeApiController.php',
        'app/zoosper-menu/src/Api/MenuApiController.php',
        'app/zoosper-page/src/Api/PageApiController.php',
    ] as $relative) {
        $source = (string) file_get_contents($root . '/' . $relative);
        expect($source, $relative)
            ->toContain('BearerPrincipalContext')
            ->toContain('->require(')
            ->not->toContain('PersonalAccessTokenAuthenticator')
            ->not->toContain("header('authorization')")
            ->not->toContain('invalid_bearer_token')
            ->not->toContain('insufficient_scope');
    }
});
