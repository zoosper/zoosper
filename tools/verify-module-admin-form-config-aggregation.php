<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper module admin form config aggregation verification\n";
print "========================================================\n\n";

$aggregator = new \Zoosper\Admin\Form\AdminFormConfigAggregator($basePath);
$config = $aggregator->aggregate();
$factory = new \Zoosper\Admin\Form\AdminFormConfigProviderFactory();
$registry = $factory->create($config);
$sections = $registry->sectionsFor('page.form', [
    'siteOptions' => '<option value="1" selected>Main Website</option>',
    'title' => 'Home',
    'slug' => 'home',
    'editorHtml' => '<input type="hidden" name="content_json" value="{&quot;blocks&quot;:[]}"><textarea name="content"></textarea>',
    'contentJson' => '{&quot;blocks&quot;:[]}',
    'metaTitle' => 'Home',
    'metaDescription' => '',
    'metaKeywords' => '',
    'canonicalUrl' => '',
    'publishChecked' => ' checked',
    'backUrl' => '/admin/pages',
]);
$html = (new \Zoosper\Admin\Form\AdminFormRenderer())->render('/admin/pages/edit?id=1', 'csrf-token', $sections);
$keys = array_map(static fn (\Zoosper\Admin\Form\AdminFormSection $section): string => $section->key, $sections);

$checks = [
    'root config/admin_forms.php exists' => is_file($basePath . '/config/admin_forms.php'),
    'page module admin_forms.php exists' => is_file($basePath . '/app/zoosper-page/config/admin_forms.php'),
    'AdminFormConfigAggregator exists' => class_exists(\Zoosper\Admin\Form\AdminFormConfigAggregator::class),
    'aggregated config has forms key' => isset($config['forms']) && is_array($config['forms']),
    'aggregated config has page.form' => isset($config['forms']['page.form']),
    'aggregated config has processors key' => isset($config['processors']) && is_array($config['processors']),
    'aggregated page.form has providers' => isset($config['forms']['page.form']) && count($config['forms']['page.form']) >= 4,
    'aggregated providers instantiate through factory' => $registry instanceof \Zoosper\Admin\Form\AdminFormProviderRegistry,
    'aggregated sections sort predictably' => $keys === ['page.details', 'page.content', 'page.seo', 'page.publishing'],
    'aggregated rendered form has content_json' => str_contains($html, 'name="content_json"'),
    'aggregated rendered form has SEO section' => str_contains($html, 'Search engine optimisation'),
    'aggregated rendered form has publishing controls' => str_contains($html, 'name="publish"'),
    'PageAdminController imports config aggregator' => str_contains($controller, 'AdminFormConfigAggregator'),
    'PageAdminController calls aggregate' => str_contains($controller, '->aggregate($rootConfig)'),
    'PageAdminController has project root helper' => str_contains($controller, 'projectRootPath'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
