<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Diagnostics\MailConfigurationInspector;

it('takes transport from SmtpConfig and keeps password redacted to a configured flag', function (): void {
    $smtp = new SmtpConfig(ConfigRepository::fromArray(['mail' => ['default' => 'smtp', 'smtp' => ['password' => 'never-render-this']]]));
    $summary = (new MailConfigurationInspector($smtp))->summary();

    expect($summary->transport)->toBe('smtp')
        ->and($summary->passwordConfigured)->toBeTrue()
        ->and((array) $summary)->not->toContain('never-render-this');
});










