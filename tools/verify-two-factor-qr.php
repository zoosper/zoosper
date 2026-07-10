<?php

declare(strict_types=1);

/**
 * Verify local QR renderer availability without generating real setup secrets.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$renderer = new \Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer();
print "Zoosper 2FA QR renderer verification\n";
print "====================================\n\n";
print '- renderer class: ' . (class_exists(\Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer::class) ? 'ok' : 'missing') . PHP_EOL;
print '- bacon/bacon-qr-code: ' . ($renderer->isAvailable() ? 'ok' : 'missing') . PHP_EOL;
print "\nResult: " . ($renderer->isAvailable() ? 'OK' : 'DEPENDENCY MISSING') . PHP_EOL;
