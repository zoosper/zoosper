<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$file = $basePath . '/app/zoosper-core/src/I18n/TranslationFileAggregator.php';
$source = (string) file_get_contents($file);

print "Zoosper translation file aggregator comment safety verification\n";
print "===============================================================\n\n";

$docBlock = '';
if (preg_match('/\/\*\*(.*?)\*\//s', $source, $matches) === 1) {
    $docBlock = (string) $matches[0];
}

$aggregator = new \Zoosper\Core\I18n\TranslationFileAggregator($basePath);
$catalogue = $aggregator->catalogue('en_AU');

$checks = [
    'TranslationFileAggregator exists' => class_exists(\Zoosper\Core\I18n\TranslationFileAggregator::class),
    'top-level PHPDoc block was found' => $docBlock !== '',
    'PHPDoc does not contain unsafe wildcard example app slash-star' => !str_contains($docBlock, 'app/*/i18n'),
    'PHPDoc does not contain unsafe wildcard example modules slash-star' => !str_contains($docBlock, 'modules/*/i18n'),
    'PHPDoc does not contain unsafe wildcard example vendor slash-star' => !str_contains($docBlock, 'vendor/*/i18n'),
    'source keeps runtime app wildcard glob' => str_contains($source, "'/app/*/i18n/'"),
    'source keeps runtime first-level module wildcard glob' => str_contains($source, "'/modules/*/i18n/'"),
    'source keeps runtime nested module wildcard glob' => str_contains($source, "'/modules/*/*/i18n/'"),
    'source keeps runtime vendor wildcard glob' => str_contains($source, "'/vendor/*/*/i18n/'"),
    'catalogue still resolves en_AU' => $catalogue->locale === 'en_AU',
    'catalogue still contains admin messages' => $catalogue->has('Page saved successfully.'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
