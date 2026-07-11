<?php
/**
 * Theme override for zoosper-page::page/view.
 *
 * @var \Zoosper\Page\Model\Page $page
 * @var callable $e
 *
 * Page body content is sanitised before persistence. Render it as HTML here;
 * do not escape it again, otherwise frontend users see literal <h2>/<p> tags.
 */
?>
<article class="page page-<?= $e($page->slug) ?>">
    <header class="page-header">
        <h1><?= $e($page->title) ?></h1>
    </header>
    <div class="page-content"><?= $page->content ?></div>
</article>
