<?php

declare(strict_types=1);

it('keeps third-party session packages behind the Zoosper Session module', function (): void {
    $root = dirname(__DIR__, 5);
    $rootComposer = json_decode((string) file_get_contents($root . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $moduleComposer = json_decode((string) file_get_contents($root . '/app/zoosper-session/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $factory = (string) file_get_contents($root . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php');
    $application = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Application.php');

    expect($rootComposer['require'])->toHaveKey('zoosper/session', 'dev-dev')
        ->not->toHaveKey('marko/session')
        ->not->toHaveKey('marko/session-file')
        ->not->toHaveKey('marko/session-database')
        ->and($moduleComposer['require'])->toHaveKey('marko/session-file', '0.8.5')
        ->and($root . '/app/zoosper-session/config/settings/session.php')->toBeFile()
        ->and($factory)->toContain('$services->has(\\SessionHandlerInterface::class)')
        ->not->toContain('Marko\\Session')
        ->not->toContain('FileSessionHandler')
        ->and($application)->toContain('private ?\\SessionHandlerInterface $sessionHandler = null')
        ->toContain('session_set_save_handler($this->sessionHandler, true)')
        ->not->toContain('Marko\\Session');
});
