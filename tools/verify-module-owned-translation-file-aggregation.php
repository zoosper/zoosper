<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper module-owned translation file aggregation verification\n";
print "=============================================================\n\n";

$aggregator = new \Zoosper\Core\I18n\TranslationFileAggregator($basePath);
$catalogue = $aggregator->catalogue('en_AU');
$translator = new \Zoosper\Core\I18n\ArrayTranslator($catalogue);

$checks = [
    'TranslationCatalogue exists' => class_exists(\Zoosper\Core\I18n\TranslationCatalogue::class),
    'TranslationFileAggregator exists' => class_exists(\Zoosper\Core\I18n\TranslationFileAggregator::class),
    'ArrayTranslator exists' => class_exists(\Zoosper\Core\I18n\ArrayTranslator::class),
    'ArrayTranslator implements TranslatorInterface' => $translator instanceof \Zoosper\Core\I18n\TranslatorInterface,
    'admin en_AU translation file exists' => is_file($basePath . '/app/zoosper-admin/i18n/en_AU.php'),
    'catalogue locale is en_AU' => $catalogue->locale === 'en_AU',
    'catalogue has page saved message' => $catalogue->has('Page saved successfully.'),
    'catalogue has CSRF message' => $catalogue->has('Unable to save page. Invalid security token.'),
    'catalogue exposes messages' => count($catalogue->messages()) >= 10,
    'translator resolves catalogue message' => $translator->translate('Page saved successfully.') === 'Page saved successfully.',
    'translator falls back to source message' => $translator->translate('Unknown message') === 'Unknown message',
    'translator replaces placeholders' => $translator->translate('Hello {name}', ['name' => 'Zoosper']) === 'Hello Zoosper',
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
