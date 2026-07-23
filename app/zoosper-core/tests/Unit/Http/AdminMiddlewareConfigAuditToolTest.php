<?php

declare(strict_types=1);

it('keeps admin middleware config audit and normalise tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-admin-middleware-config.php')->toBeFile();
    expect($root . '/tools/normalise-admin-middleware-config.php')->toBeFile();
});
