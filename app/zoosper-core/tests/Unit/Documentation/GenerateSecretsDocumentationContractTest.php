<?php

declare(strict_types=1);

it('documents the canonical parser atomic write and redaction boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $configuration = (string) file_get_contents($root . '/docs/configuration.md');
    $cli = (string) file_get_contents($root . '/docs/cli.md');
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $changelog = (string) file_get_contents($root . '/CHANGELOG.md');

    foreach ([$configuration, $cli, $roadmap, $changelog] as $document) {
        expect($document)->toContain('0600');
    }

    expect($configuration)->toContain('canonical bootstrap parser')
        ->toContain('atomically replaces')
        ->toContain('do not expose existing or generated secret values')
        ->and($cli)->toContain('duplicate targeted keys')
        ->toContain('checked atomic `0600` writes')
        ->toContain('prefer `--write`')
        ->and($roadmap)->toContain('canonical quoted/comment/`export` parsing')
        ->and($changelog)->toContain('Closed SR-6 GenerateSecrets environment-file hardening');
});
