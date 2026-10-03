<?php
declare(strict_types=1);
it('pins every external workflow action to a full immutable commit SHA', function (): void {
    $root = dirname(__DIR__, 5);
    $workflows = glob($root . '/.github/workflows/*.{yml,yaml}', GLOB_BRACE) ?: [];
    expect($workflows)->not->toBeEmpty();
    foreach ($workflows as $workflow) {
        $source = (string) file_get_contents($workflow);
        preg_match_all('/^\s*uses:\s*([^\s#]+)@([^\s#]+)/m', $source, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            expect($match[2], $workflow . ': ' . $match[0])->toMatch('/^[0-9a-f]{40}$/');
        }
    }
});
