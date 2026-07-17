<?php

declare(strict_types=1);

/**
 * Module-owned frontend page view template.
 *
 * @var object $page
 * @var callable $e
 * @var string|null $renderedContent Page body HTML prepared by PageRenderer.
 */
$bodyHtml = $renderedContent ?? $page->content ?? '';
?>
<article class="cms-page cms-page--module-view">
    <header class="cms-page__header">
        <h1><?= $e($page->title ?? '') ?></h1>
    </header>

    <div class="cms-page__content">
        <?= $bodyHtml ?>
    </div>
</article>
