<?php

declare(strict_types=1);

it('keeps the completed Two Factor dead-code cohort absent', function (): void {
    $root = dirname(__DIR__, 3);
    foreach ([
        'src/Service/AdminTwoFactorSetupNotificationService.php',
        'src/Service/TotpProvisioningUriGenerator.php',
        'src/Value/TotpSetup.php',
    ] as $file) {
        expect($root . '/' . $file)->not->toBeFile();
    }

    expect((string) file_get_contents($root . '/src/Repository/AdminTwoFactorResetRepository.php'))
        ->not->toContain('function missingTables(')
        ->and((string) file_get_contents($root . '/src/Service/AdminTwoFactorLoginRedirectService.php'))
        ->not->toContain('function requiresSetup(');
});
