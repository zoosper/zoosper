<?php

declare(strict_types=1);

it('consumes pending enrolment material before OTP verification', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-two-factor/src/Controller/AdminTwoFactorSetupController.php',
    );

    $read = strpos($source, <<<'SOURCE'
$_SESSION['pending_2fa_secret']
SOURCE);
    $consume = strpos($source, <<<'SOURCE'
unset($_SESSION['pending_2fa_secret'])
SOURCE);
    $confirm = strpos($source, '$this->enrolment->confirm(');

    expect($read)->not->toBeFalse()
        ->and($consume)->not->toBeFalse()
        ->and($confirm)->not->toBeFalse()
        ->and($read)->toBeLessThan($consume)
        ->and($consume)->toBeLessThan($confirm)
        ->and($source)->toContain(
            'Invalid or already-used authenticator setup. Start setup again.',
        );
});
