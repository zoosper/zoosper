<?php

declare(strict_types=1);

use Zoosper\Auth\PasswordReset\AdminPasswordResetUrlBuilder;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Url\AdminUrlGenerator;

it('builds an encoded canonical Admin reset URL from the configured origin', function (): void {
    $builder = new AdminPasswordResetUrlBuilder(
        'https://admin.example.test/',
        new AdminUrlGenerator(ConfigRepository::fromArray(['admin' => ['base_path' => '/control-centre']])),
    );
    expect($builder->build('zp_reset_abc_123'))
        ->toBe('https://admin.example.test/control-centre/reset-password?token=zp_reset_abc_123');
});

it('rejects unsafe or non-absolute application origins', function (string $origin): void {
    $builder = new AdminPasswordResetUrlBuilder($origin, new AdminUrlGenerator(ConfigRepository::fromArray([])));
    expect(fn () => $builder->build('token'))->toThrow(InvalidArgumentException::class);
})->with(['', '/relative', 'javascript:alert(1)', 'https://user:pass@example.test', 'https://example.test/?x=1']);
