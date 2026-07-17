<?php
/**
 * Theme override for zoosper-page::page/view.
 *
 * @var \Zoosper\Page\Model\Page $page
 * @var callable $e
 * @var string|null $renderedContent Page body HTML prepared by PageRenderer.
 */
$bodyHtml = $renderedContent ?? $page->content;
?>
<article class="page page-<?= $e($page->slug) ?>">
    <header class="page-header">
        <h1><?= $e($page->title) ?></h1>
    </header>
    <div class="page-content"><?= $bodyHtml ?></div>
</article>
