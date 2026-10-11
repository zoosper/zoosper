<?php

declare(strict_types=1);

it('narrows the optional two-factor challenge service before issuing a login challenge', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/LoginController.php');

    expect($source)
        ->toContain('$twoFactorChallenge = $this->twoFactorChallenge;')
        ->toContain('if ($this->requiresTwoFactorChallenge($user) && $twoFactorChallenge !== null)')
        ->toContain('$twoFactorChallenge->issue($user->id)')
        ->not->toContain('$this->twoFactorChallenge->issue($user->id)');
});
