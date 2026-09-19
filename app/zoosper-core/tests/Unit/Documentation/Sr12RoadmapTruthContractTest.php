<?php

declare(strict_types=1);

it('keeps completed release security Grid Form asset Media and documentation work closed', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $readme = (string) file_get_contents($root . '/README.md');
    $agents = (string) file_get_contents($root . '/AGENTS.md');
    $seo = (string) file_get_contents($root . '/app/zoosper-seo/README.md');

    expect($roadmap)
        ->toContain('SR-11 Media picker HTTP acceptance')
        ->toContain('Production source deployment is documented and executable')
        ->toContain('Current genuinely open delivery work')
        ->not->toContain('every internal module dependency still uses unconstrained `*@dev`')
        ->not->toContain('only actually used by the Page form')
        ->not->toContain('Add DOM behavioural coverage for column drag')
        ->not->toContain('Moving GD derivative processing to an asynchronous queue/worker remains a separate operational follow-up')
        ->not->toContain('Complete documentation-site source expansion and automated publishing as a separate bounded phase')
        ->and($readme)
        ->not->toContain('CI test suite execution against an active MySQL service container is being finalized')
        ->not->toContain('Automated secret generation and comprehensive boot-time production validation are being finalized')
        ->not->toContain('Absolute session lifetime controls and concurrent session limits are in progress')
        ->and($agents)
        ->toContain('Current standalone package inventory is documented in `docs/modules.md`')
        ->not->toContain('logger is next in line')
        ->and($seo)
        ->toContain('Sitemap and robots requests are stateless before session initialisation')
        ->not->toContain('inherit application session bootstrap behaviour');
});
