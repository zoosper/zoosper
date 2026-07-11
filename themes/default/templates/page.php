<?php

declare(strict_types=1);

/**
 * Default CMS page template.
 *
 * Page content remains server-rendered for SEO. Do not move SEO-critical content
 * such as title, H1, body, canonical data or structured data behind AJAX.
 *
 * @var object $page
 * @var callable $e
 */
?>
<article class="cms-page">
    <header class="cms-page__header">
        <h1><?= $e($page->title ?? '') ?></h1>
    </header>

    <div class="cms-page__content">
        <?= $page->content ?? '' ?>
    </div>
</article>
