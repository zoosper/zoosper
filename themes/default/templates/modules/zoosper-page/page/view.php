<?php
/**
 * Theme override for zoosper-page::page/view.
 * @var callable $e
 * @var \Zoosper\Page\Model\Page $page
 */
?>
<main class="page-shell">
    <h1><?= $e($page->title) ?></h1>
    <div class="page-content"><?= nl2br($e($page->content)) ?></div>
</main>
