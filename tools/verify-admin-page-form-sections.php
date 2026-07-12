<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper admin page form section verification\n";
print "============================================\n\n";

$checks = [
    'page form uses sectioned class' => str_contains($controller, 'page-form--sectioned'),
    'Page details section exists' => str_contains($controller, 'Page details') && str_contains($controller, 'page-form__section--details'),
    'Content section exists' => str_contains($controller, 'Content') && str_contains($controller, 'page-form__section--content'),
    'SEO section preserved' => str_contains($controller, 'Search engine optimisation') && str_contains($controller, 'page-form__section--seo'),
    'Publishing section exists' => str_contains($controller, 'Publishing') && str_contains($controller, 'page-form__section--publishing'),
    'content_json hidden field path preserved' => str_contains($controller, 'content_json'),
    'meta_title field preserved' => str_contains($controller, 'name="meta_title"'),
    'meta_description field preserved' => str_contains($controller, 'name="meta_description"'),
    'meta_keywords field preserved' => str_contains($controller, 'name="meta_keywords"'),
    'canonical_url field preserved' => str_contains($controller, 'name="canonical_url"'),
    'publish checkbox preserved' => str_contains($controller, 'name="publish"'),
    'save action preserved' => str_contains($controller, 'Save page'),
    'back action preserved' => str_contains($controller, 'Back'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
