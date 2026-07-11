<?php

declare(strict_types=1);

/**
 * Verify default frontend Latte templates and fallback files.
 */

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper frontend Latte template verification\n";
print "===========================================\n\n";

$required = [
    'themes/default/templates/layout.latte',
    'themes/default/templates/page.latte',
    'app/zoosper-page/resources/views/page/view.latte',
    'themes/default/templates/layout.php',
    'app/zoosper-page/resources/views/page/view.php',
];

$failed = false;
foreach ($required as $file) {
    $exists = is_file($basePath . '/' . $file);
    print '- ' . $file . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$pageRendererPath = $basePath . '/app/zoosper-page/src/Service/PageRenderer.php';
$pageRenderer = is_file($pageRendererPath) ? (string) file_get_contents($pageRendererPath) : '';
$usesExtensionlessLayout = str_contains($pageRenderer, "renderLayout('layout'") || str_contains($pageRenderer, 'renderLayout("layout"');
print '- PageRenderer extensionless layout: ' . ($usesExtensionlessLayout ? 'ok' : 'missing') . PHP_EOL;
$failed = $failed || !$usesExtensionlessLayout;

try {
    $engine = new \Zoosper\Theme\Template\Engine\LatteTemplateEngine($basePath . '/var/cache/templates');
    $cdn = new class {
        public function staticAsset(string $path): string
        {
            return '/static' . $path;
        }

        public function dynamicForContext(string $path, mixed $siteContext): string
        {
            return $path;
        }
    };

    $html = $engine->renderFile($basePath . '/themes/default/templates/layout.latte', [
        'page' => (object) ['title' => 'Latte Page', 'metaTitle' => ''],
        'site' => (object) ['name' => 'Zoosper'],
        'siteContext' => (object) ['locale' => 'en_AU'],
        'cdn' => $cdn,
        'content' => '<article>Latte content</article>',
        'versionLabel' => 'Phase 0.62',
    ]);

    $rendered = str_contains($html, 'Latte Page') && str_contains($html, '/static/themes/default/assets/css/app.css');
    print '- layout.latte render: ' . ($rendered ? 'ok' : 'check') . PHP_EOL;
    $failed = $failed || !$rendered;
} catch (Throwable $exception) {
    print '- layout.latte render: FAIL - ' . $exception->getMessage() . PHP_EOL;
    $failed = true;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
