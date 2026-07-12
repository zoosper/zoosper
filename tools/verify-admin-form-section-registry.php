<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$controller = (string) file_get_contents($basePath . '/app/zoosper-admin/src/Controller/PageAdminController.php');

print "Zoosper admin form section registry verification\n";
print "================================================\n\n";

$registry = (new \Zoosper\Admin\Form\AdminFormProviderRegistry())
    ->add(new \Zoosper\Page\Admin\Form\PageDetailsSectionProvider())
    ->add(new \Zoosper\Page\Admin\Form\PageContentSectionProvider())
    ->add(new \Zoosper\Page\Admin\Form\PageSeoSectionProvider())
    ->add(new \Zoosper\Page\Admin\Form\PagePublishingSectionProvider());

$sections = $registry->sectionsFor('page.form', [
    'siteOptions' => '<option value="1">Main</option>',
    'title' => 'Home',
    'slug' => 'home',
    'editorHtml' => '<textarea name="content"></textarea><input type="hidden" name="content_json">',
    'metaTitle' => 'Home',
    'metaDescription' => '',
    'metaKeywords' => '',
    'canonicalUrl' => '',
    'publishChecked' => ' checked',
    'backUrl' => '/admin/pages',
]);
$html = (new \Zoosper\Admin\Form\AdminFormRenderer())->render('/admin/pages/edit?id=1', 'token', $sections);
$keys = array_map(static fn (\Zoosper\Admin\Form\AdminFormSection $section): string => $section->key, $sections);

$checks = [
    'AdminFormSection exists' => class_exists(\Zoosper\Admin\Form\AdminFormSection::class),
    'AdminFormSectionProviderInterface exists' => interface_exists(\Zoosper\Admin\Form\AdminFormSectionProviderInterface::class),
    'AdminFormProviderRegistry exists' => class_exists(\Zoosper\Admin\Form\AdminFormProviderRegistry::class),
    'AdminFormRenderer exists' => class_exists(\Zoosper\Admin\Form\AdminFormRenderer::class),
    'page.details provider exists' => class_exists(\Zoosper\Page\Admin\Form\PageDetailsSectionProvider::class),
    'page.content provider exists' => class_exists(\Zoosper\Page\Admin\Form\PageContentSectionProvider::class),
    'page.seo provider exists' => class_exists(\Zoosper\Page\Admin\Form\PageSeoSectionProvider::class),
    'page.publishing provider exists' => class_exists(\Zoosper\Page\Admin\Form\PagePublishingSectionProvider::class),
    'sections sort predictably' => $keys === ['page.details', 'page.content', 'page.seo', 'page.publishing'],
    'rendered form preserves content_json' => str_contains($html, 'name="content_json"'),
    'rendered form preserves SEO section' => str_contains($html, 'Search engine optimisation'),
    'rendered form preserves publish controls' => str_contains($html, 'name="publish"'),
    'PageAdminController uses registry' => str_contains($controller, 'AdminFormProviderRegistry'),
    'PageAdminController uses renderer' => str_contains($controller, 'AdminFormRenderer'),
    'PageAdminController has default section registry' => str_contains($controller, 'defaultPageFormSectionRegistry'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
