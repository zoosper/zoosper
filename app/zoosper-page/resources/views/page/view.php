<?php

declare(strict_types=1);

/**
 * Module-owned frontend page view template.
 *
 * This template intentionally server-renders the primary page title and content
 * for SEO and Core Web Vitals. Dynamic/private fragments can be loaded later
 * through AJAX endpoints, but core CMS content should remain present in the
 * initial HTML response.
 *
 * @var object $page
 * @var callable $e
 */
?>
<article class="cms-page cms-page--module-view">
    <header class="cms-page__header">
        <h1><?= $e($page->title ?? '') ?></h1>
    </header>

    <div class="cms-page__content">
        <?= $page->content ?? '' ?>
    </div>
</article>
