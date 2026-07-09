<?php
/**
 * @var callable $e
 * @var \Zoosper\Page\Model\Page $page
 */
?>
<article class="page-content-block">
    <h1><?= $e($page->title) ?></h1>
    <div><?= nl2br($e($page->content)) ?></div>
</article>
