<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/page-content-schema-db.php';

$pdo = zoosper_phase077_pdo($basePath);
$model = (string) file_get_contents($basePath . '/app/zoosper-page/src/Model/Page.php');
$repository = (string) file_get_contents($basePath . '/app/zoosper-page/src/Repository/PageRepository.php');
$pageSeoProvider = is_file($basePath . '/app/zoosper-page/src/Admin/Form/PageSeoSectionProvider.php')
    ? (string) file_get_contents($basePath . '/app/zoosper-page/src/Admin/Form/PageSeoSectionProvider.php')
    : '';
$pageFormHtml = zoosper_phase0841_render_page_form();

print "Zoosper page SEO metadata verification\n";
print "======================================\n\n";

$checks = [
    'schema file exists' => is_file($basePath . '/database/schema/page_seo_metadata.php'),
    'Page model has metaTitle' => str_contains($model, 'metaTitle'),
    'Page model has metaDescription' => str_contains($model, 'metaDescription'),
    'Page model has metaKeywords' => str_contains($model, 'metaKeywords'),
    'Page model has canonicalUrl' => str_contains($model, 'canonicalUrl'),
    'Repository references meta_title' => str_contains($repository, 'meta_title'),
    'Repository references meta_description' => str_contains($repository, 'meta_description'),
    'Repository references meta_keywords' => str_contains($repository, 'meta_keywords'),
    'Repository references canonical_url' => str_contains($repository, 'canonical_url'),
    'PageSeoSectionProvider exists' => class_exists(\Zoosper\Page\Admin\Form\PageSeoSectionProvider::class),
    'PageSeoSectionProvider renders SEO title' => str_contains($pageSeoProvider, 'Search engine optimisation'),
    'Rendered page form has SEO section' => str_contains($pageFormHtml, 'Search engine optimisation'),
    'Rendered page form has meta_title field' => str_contains($pageFormHtml, 'name="meta_title"'),
    'Rendered page form has meta_description field' => str_contains($pageFormHtml, 'name="meta_description"'),
    'Rendered page form has meta_keywords field' => str_contains($pageFormHtml, 'name="meta_keywords"'),
    'Rendered page form has canonical_url field' => str_contains($pageFormHtml, 'name="canonical_url"'),
    'pages.meta_title exists' => zoosper_phase077_column_exists($pdo, 'pages', 'meta_title'),
    'pages.meta_description exists' => zoosper_phase077_column_exists($pdo, 'pages', 'meta_description'),
    'pages.meta_keywords exists' => zoosper_phase077_column_exists($pdo, 'pages', 'meta_keywords'),
    'pages.canonical_url exists' => zoosper_phase077_column_exists($pdo, 'pages', 'canonical_url'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

function zoosper_phase0841_render_page_form(): string
{
    $sections = (new \Zoosper\Admin\Form\AdminFormProviderRegistry())
        ->add(new \Zoosper\Page\Admin\Form\PageDetailsSectionProvider())
        ->add(new \Zoosper\Page\Admin\Form\PageContentSectionProvider())
        ->add(new \Zoosper\Page\Admin\Form\PageSeoSectionProvider())
        ->add(new \Zoosper\Page\Admin\Form\PagePublishingSectionProvider())
        ->sectionsFor('page.form', [
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

    return (new \Zoosper\Admin\Form\AdminFormRenderer())->render('/admin/pages/edit?id=1', 'csrf-token', $sections);
}
