<?php

declare(strict_types=1);

use Zoosper\Core\Environment\EnvLineParser;
use Zoosper\Core\Environment\SecureEnvFileEditor;

it('parses canonical quoted export and comment forms', function (): void {
    expect(EnvLineParser::parseAssignment('export APP_KEY="change-me"')?->value)->toBe('change-me')
        ->and(EnvLineParser::parseAssignment("APP_KEY='value # retained'")?->value)->toBe('value # retained')
        ->and(EnvLineParser::parseAssignment('APP_KEY=value # removed')?->value)->toBe('value');
});

it('preserves unrelated formatting and publishes with mode 0600', function (): void {
    $directory = sys_get_temp_dir() . '/zoosper-env-editor-' . bin2hex(random_bytes(6));
    mkdir($directory, 0700, true);
    $path = $directory . '/.env';
    file_put_contents($path, "# retained\r\nexport APP_KEY = change-me # rotate\r\nOTHER = 'keep # this'\r\n");
    chmod($path, 0644);

    try {
        $status = (new SecureEnvFileEditor())->update(
            $path,
            ['APP_KEY' => 'base64:new-secret'],
            static fn (string $value): bool => $value === 'change-me',
        );
        expect(file_get_contents($path))->toBe("# retained\r\nexport APP_KEY = base64:new-secret # rotate\r\nOTHER = 'keep # this'\r\n")
            ->and(fileperms($path) & 0777)->toBe(0600)
            ->and($status['APP_KEY'])->toBe('Updated existing line');
    } finally {
        @unlink($path);
        @rmdir($directory);
    }
});

it('rejects duplicate generated keys without modifying the file', function (): void {
    $directory = sys_get_temp_dir() . '/zoosper-env-editor-' . bin2hex(random_bytes(6));
    mkdir($directory, 0700, true);
    $path = $directory . '/.env';
    $original = "APP_KEY=one\nexport APP_KEY=two\nOTHER=keep\n";
    file_put_contents($path, $original);

    try {
        expect(fn () => (new SecureEnvFileEditor())->update(
            $path,
            ['APP_KEY' => 'base64:new-secret'],
            static fn (): bool => true,
        ))->toThrow(RuntimeException::class, 'assigned more than once')
            ->and(file_get_contents($path))->toBe($original);
    } finally {
        @unlink($path);
        @rmdir($directory);
    }
});
