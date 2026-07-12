<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$configPath = $basePath . '/config/admin_forms.php';
$config = is_file($configPath) ? require $configPath : [];
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper admin form section registration verification\n";
print "====================================================\n\n";

$factory = new \Zoosper\Admin\Form\AdminFormConfigProviderFactory();
$registry = $factory->create($config, [
    'page.form' => [
        \Zoosper\Page\Admin\Form\PageDetailsSectionProvider::class,
        \Zoosper\Page\Admin\Form\PageContentSectionProvider::class,
        \Zoosper\Page\Admin\Form\PageSeoSectionProvider::class,
        \Zoosper\Page\Admin\Form\PagePublishingSectionProvider::class,
    ],
]);
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
    'config/admin_forms.php exists' => is_file($configPath),
    'AdminFormConfigProviderFactory exists' => class_exists(\Zoosper\Admin\Form\AdminFormConfigProviderFactory::class),
    'admin_forms config has page.form handle' => isset(($config['forms'] ?? [])['page.form']),
    'factory creates registry' => $registry instanceof \Zoosper\Admin\Form\AdminFormProviderRegistry,
    'configured sections sort predictably' => $keys === ['page.details', 'page.content', 'page.seo', 'page.publishing'],
    'configured rendered form has content_json' => str_contains($html, 'name="content_json"'),
    'configured rendered form has SEO section' => str_contains($html, 'Search engine optimisation'),
    'configured rendered form has publishing controls' => str_contains($html, 'name="publish"'),
    'PageAdminController imports config factory' => str_contains($controller, 'AdminFormConfigProviderFactory'),
    'PageAdminController reads admin_forms config' => str_contains($controller, "array('admin_forms')"),
    'PageAdminController keeps fallback providers' => str_contains($controller, 'PageDetailsSectionProvider::class'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
